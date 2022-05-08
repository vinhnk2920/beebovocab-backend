<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function findByEmailOrPhone(Request $request)
    {
        $phone = $request->input('phone');
        $email = $request->input('email');

        if (is_null($phone) && is_null($email)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần nhập đủ thông tin!'
            ]);
        }

        if (!is_null($phone)) {
            $users = DB::table('users')->where('phone', $phone)->get()->toArray();
        } else if (!is_null($email)) {
            $users = DB::table('users')->where('email', $email)->get()->toArray();
        }

        if (empty($users)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có dữ liệu thỏa mãn!'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Thành công!',
            'user' => $users
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $user_id = $request->input('user_id');
        $deleted_user_id = $request->input('deleted_user_id');

        if (is_null($user_id) && is_null($deleted_user_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn phải nhập đầy đủ dữ liệu!'
            ]);
        }

        if (DB::table('users')->where('id', $deleted_user_id)->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Xóa người dùng thành công!'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Xóa người dùng thất bại!'
            ]);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user_id = $request->input('user_id');

        if (is_null($user_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng không tồn tại'
            ]);
        }

        $users = DB::table('users')->simplePaginate();

        if (empty($users)) {
            return response()->json([
                'success' => true,
                'message' => 'Thất bại',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Thành công',
            'users' => $users
        ]);
    }
}
