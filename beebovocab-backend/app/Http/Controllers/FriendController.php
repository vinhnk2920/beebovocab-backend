<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class FriendController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function findByPhoneOrEmail(Request $request)
    {
        $phone = $request->input('phone');
        $email = $request->input('email');
        if (is_null($phone) && is_null($email)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần nhập đủ thông tin!'
            ]);
        }
        $friends = [];
        if (!is_null($phone)) {
            $friends = DB::table('users')->where('phone', $phone)->get()->toArray();
        } else if (!is_null($email)) {
            $friends = DB::table('users')->where('email', $email)->get()->toArray();
        }
        if (empty($friends)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có dữ liệu thỏa mãn!'
            ]);
        }

        if ($friends[0]->id === $request->input('request_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Trùng với username hiện tại!'
            ]);
        }
        $check_status = DB::table('friend_relationships')->where('user_request_id', $request->input('request_id'))->where('user_receive_id', $friends[0]->id)->select('status')->get()->toArray();
        $check_request = DB::table('friend_relationships')->where('user_request_id', $friends[0]->id)->where('user_receive_id', $request->input('request_id'))->select('status')->get()->toArray();
        if (!empty($check_request[0])) {
            return response()->json([
                'success' => true,
                'message' => 'Thành công!',
                'data' => $friends,
                'status' => -1
            ]);
        } else if (!empty($check_status[0])) {
            return response()->json([
                'success' => true,
                'message' => 'Thành công!',
                'data' => $friends,
                'status' => $check_status[0]->status
            ]);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Thành công!',
                'data' => $friends,
                'status' => null
            ]);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addFriendRequest(Request $request)
    {
        $request_id = $request->input('user_request_id');
        $receive_id = $request->input('user_receive_id');

        if (is_null($request_id) && is_null($receive_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn phải nhập đầy đủ dữ liệu!'
            ]);
        }
        $addFriend = DB::table('friend_relationships')->insertGetId([
            'user_request_id' => $request_id,
            'user_receive_id' => $receive_id,
            'status' => 0 // add friend
        ]);
        if ($addFriend) {
            return response()->json([
                'success' => true,
                'message' => 'Thêm bạn bè thành công!!!',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Thêm bạn bè thất bại!!!',
            ]);
        }

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateFriendStatus(Request $request)
    {
        $request_id = $request->input('user_request_id');
        $receive_id = $request->input('user_receive_id');
        $status = $request->input('status');

        if (is_null($request_id) && is_null($receive_id) && is_null($status)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn phải nhập đầy đủ dữ liệu!'
            ]);
        }

        $isUpdate = DB::table('friend_relationships')->whereIn('user_request_id', [$request_id, $receive_id])->whereIn('user_receive_id', [$request_id, $receive_id])->update(['status' => $status]);
        if($isUpdate){
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật hành công!'
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Cập nhật thất bại!'
        ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteFriendRequest(Request $request)
    {
        $request_id = $request->input('user_request_id');
        $receive_id = $request->input('user_receive_id');

        if (is_null($request_id) && is_null($receive_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn phải nhập đầy đủ dữ liệu!'
            ]);
        }

        if (DB::table('friend_relationships')->where('user_request_id', $request_id)->where('user_receive_id', $receive_id)->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Xóa kết bạn thành công'
            ]);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $user_id = $request->input('user_id');

        if (is_null($user_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng không tồn tại'
            ]);
        }

        $friends_1 = DB::table('friend_relationships')->where('status', 1)->where('user_request_id', $user_id)->join('users', 'friend_relationships.user_receive_id', '=', 'users.id')->get()->toArray();
        $friends_2 = DB::table('friend_relationships')->where('status', 1)->where('user_receive_id', $user_id)->join('users', 'friend_relationships.user_request_id', '=', 'users.id')->get()->toArray();
        $result = array_merge($friends_1, $friends_2);

        return response()->json([
            'success' => true,
            'message' => 'Thành công',
            'friends' => $result
        ]);
    }
}
