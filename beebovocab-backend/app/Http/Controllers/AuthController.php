<?php

namespace App\Http\Controllers;

use App\Models\User;
use Cache;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Hash;
use Tool;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Hàm __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['store']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $firebaseIdToken = $request->input('id_token');
        $getUserDataFromFirebase = $this->getUserDataFromFirebase($firebaseIdToken);

        if (!$getUserDataFromFirebase) {
            return response()->json([
                'status' => 'error',
                'message' => 'Thông tin đăng nhập không chính xác!'
            ], 401);
        } else {
            $firebaseUserData = $getUserDataFromFirebase->users[0];
            $firebaseId = $firebaseUserData->localId ?? null;
            $firebaseProvider = $firebaseUserData->providerUserInfo[0]->providerId ?? 'google.com';

            // Check xem provider là gì
            $firebasePhoneId = $firebaseProvider === 'phone' ? $firebaseId : null;
            $firebaseGoogleId = $firebaseProvider === 'google.com' ? $firebaseId : null;
            $firebaseFacebookId = $firebaseProvider === 'facebook.com' ? $firebaseId : null;

            $firebaseEmail = $firebaseUserData->providerUserInfo[0]->email ?? ($firebaseUserData->email ?? null);
            $firebasePhotoUrl = isset($firebaseUserData->providerUserInfo[0]->photoUrl) ? $firebaseUserData->providerUserInfo[0]->photoUrl . '?type=large' : (isset($firebaseUserData->photoUrl) ? $firebaseUserData->photoUrl . '?type=large' : null);
            $firebasePhone = isset($firebaseUserData->phoneNumber) ? preg_replace("/\+84/", "0", $firebaseUserData->phoneNumber) : null;
            $firebaseDisplayName = $firebaseUserData->displayName ?? $firebasePhone;

            $firebasePhotoUrl = !empty($firebasePhotoUrl) ? $firebasePhotoUrl : 'https://res.cloudinary.com/jasplus/image/upload/v1627463735/avatardefault_92824_bfazah.png';

            if (empty($firebaseId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Có lỗi xảy ra khi lấy fId. Vui lòng thử lại sau'
                ], 401);
            }

            $user_info = (object)array(
                'firebase_id' => $firebaseId,
                'firebase_phone_id' => $firebasePhoneId,
                'firebase_google_id' => $firebaseGoogleId,
                'firebase_facebook_id' => $firebaseFacebookId,
                'name' => $firebaseDisplayName,
                'email' => $firebaseEmail,
                'avatar' => $firebasePhotoUrl,
                'phone' => $firebasePhone,
            );

            $user = $this->createUser($user_info);
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'status' => 'success',
                'message' => 'Đăng nhập thành công',
                'data' => $user,
                'token' => $token
            ])->header('Access-Control-Allow-Headers', 'Authorization')->header('Access-Control-Expose-Headers', 'Authorization')->header('Authorization', $token);
        }
    }

    /**
     * Lấy thông tin user từ firebase
     *
     * @param $id_token
     * @return false|mixed
     */
    public function getUserDataFromFirebase($id_token)
    {
        $api_key = 'AIzaSyAfWPHZQaE2INyFuPIH2GPVbPG-AmL74aU';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.googleapis.com/identitytoolkit/v3/relyingparty/getAccountInfo?key=" . $api_key,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\"idToken\":\"$id_token\"}",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        $response = json_decode($response);

        if ($err || !isset($response->users[0])) {
            return false;
        }
        return $response;
    }


    /**
     * Refresh a token.
     *
     * @return JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    public function createUser($userInfoFromFirebase)
    {
        $userQuery = User::query();
        if (!empty($userInfoFromFirebase->email)) {
            $userQuery->orWhere('email', $userInfoFromFirebase->email);
        }
        if (!empty($userInfoFromFirebase->phone)) {
            $userQuery->orWhere('phone', $userInfoFromFirebase->phone);
        }
        $userQuery->orWhere('firebase_google_id', $userInfoFromFirebase->firebase_id)->orWhere('firebase_facebook_id', $userInfoFromFirebase->firebase_id)->orWhere('firebase_phone_id', $userInfoFromFirebase->firebase_id);

        // Nếu user tồn tại rồi thì lấy trong DB ra
        $userInfoInDatabase = $userQuery->first();

        $lock = Cache::lock('fir_user_' . $userInfoFromFirebase->firebase_id . '_lock', 10);
        try {
            if ($lock->get()) {
                // Nếu user chưa tồn tại thì tự tạo tài khoản mới
                if (!$userInfoInDatabase) {
                    $userInfoInDatabase = new User();
                    $userInfoInDatabase->name = $userInfoFromFirebase->name;
                    $userInfoInDatabase->email = $userInfoFromFirebase->email;
                    $userInfoInDatabase->phone = $userInfoFromFirebase->phone;
                    $userInfoInDatabase->avatar = $userInfoFromFirebase->avatar;

                    $userInfoInDatabase->password = Hash::make(time() . rand(6666, 8888) . 'matkh4ukh0do4n');
                }

                // Nếu phone/google/facebook/avatar chưa có trong DB, mà request lại có, thì update thêm vào DB
                if (empty($userInfoInDatabase->email) && !empty($userInfoFromFirebase->email)) {
                    $userInfoInDatabase->email = $userInfoFromFirebase->email;
                }
                if (empty($userInfoInDatabase->phone) && !empty($userInfoFromFirebase->phone)) {
                    $userInfoInDatabase->phone = $userInfoFromFirebase->phone;
                }
                if (empty($userInfoInDatabase->name) && !empty($userInfoFromFirebase->name)) {
                    $userInfoInDatabase->name = $userInfoFromFirebase->name;
                }
                // Nếu phone/google/facebook firebase id chưa có trong DB, mà request lại có, thì update thêm vào DB
                if (empty($userInfoInDatabase->firebase_phone_id) && !empty($userInfoFromFirebase->firebase_phone_id)) {
                    $userInfoInDatabase->firebase_phone_id = $userInfoFromFirebase->firebase_phone_id;
                }
                if (empty($userInfoInDatabase->firebase_google_id) && !empty($userInfoFromFirebase->firebase_google_id)) {
                    $userInfoInDatabase->firebase_google_id = $userInfoFromFirebase->firebase_google_id;
                }
                if (empty($userInfoInDatabase->firebase_facebook_id) && !empty($userInfoFromFirebase->firebase_facebook_id)) {
                    $userInfoInDatabase->firebase_facebook_id = $userInfoFromFirebase->firebase_facebook_id;
                }
                // App FB nhiều lúc die, firebase user bị mất, phải tự update lại
                if (!empty($userInfoInDatabase->firebase_facebook_id) && !empty($userInfoFromFirebase->firebase_facebook_id) && $userInfoInDatabase->firebase_facebook_id !== $userInfoFromFirebase->firebase_facebook_id) {
                    $userInfoInDatabase->firebase_facebook_id = $userInfoFromFirebase->firebase_facebook_id;
                    $userInfoInDatabase->avatar = $userInfoFromFirebase->avatar;
                }

                $userInfoInDatabase->save();
                $lock->release();

                return $userInfoInDatabase;
            }
        } catch (LockTimeoutException $e) {
            return false;
        } finally {
            optional($lock)->release();
        }
    }

    public function randomNumber($digits = 4): int
    {
        return rand(pow(10, $digits - 1), pow(10, $digits) - 1);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request)
    {
        auth()->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out'
        ]);
    }
}
