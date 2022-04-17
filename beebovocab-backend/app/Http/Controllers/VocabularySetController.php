<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


class VocabularySetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user_id = $request->input('created_user_id');
        if (is_null($user_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng không tồn tại'
            ]);
        }
        $vocabulary = DB::table('vocabulary_sets')->where('created_user_id', $user_id)->simplePaginate(15);
        if (empty($vocabulary)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có dữ liệu'
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Thành công!!!',
            'data' => [
                'vocabulary' => $vocabulary,
            ],
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showDefaultSet(Request $request)
    {
        $user_ids = DB::table('users')->where('role', 'admin')->get()->pluck('id');
        $vocabulary = DB::table('vocabulary_sets')->whereIn('created_user_id', $user_ids)->simplePaginate(15);

        if (empty($vocabulary)) {
            return response()->json([
                'success' => true,
                'message' => 'Không có dữ liệu'
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Thành công',
            'data' => [
                'vocabulary' => $vocabulary,
            ],
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showSet($id)
    {
        $vocabulary = DB::table('vocabulary_sets')->where('id', $id)->get()->toArray();
        if (empty($vocabulary)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có dữ liệu'
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Thành công',
            'data' => [
                'vocabulary' => $vocabulary,
            ],
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function findByTopicId($topic_id)
    {
        if(empty($topic_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn phải nhập id của chủ đề!'
            ]);
        }

        $vocabularySet = DB::table('vocabulary_sets')->where('topic_id', $topic_id)->simplePaginate(15);
        if (empty($vocabularySet)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có bộ từ thỏa mãn!'
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Thành công!',
            'data' => [
                'vocabulary_sets' => $vocabularySet,
            ],
        ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:191',
            'description' => 'required|max:255',
            'avatar_image' => 'required|max:255',
            'created_user_id' => 'required|max:255',
        ]);
        $vocabulary_set_id = DB::table('vocabulary_sets')->insertGetId([
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'avatar_image' => $request->get('avatar_image'),
            'created_user_id' => $request->get('created_user_id'),
            'topic_id' => $request->get('topic_id', null),
        ]);
        if (empty($vocabulary_set_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra, thất bại'
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Tạo bộ từ thành công'
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
        $id = $request->get('id');
        if(is_null($id)) {
            return response()->json([
                'success' => false,
                'message' => 'Không tồn tại bộ từ cần sửa!'
            ], 200);
        }

        $isUpdate = DB::table('vocabulary_sets')->where('id',$id)->update($request->all());
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
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //
        $vocabulary_sets_id = $request->get('vocabulary_sets_id');
        if (empty($vocabulary_sets_id)) {
            return response()->json([
                'success' => true,
                'message' => 'Không tìm thấy bộ từ'
            ]);
        }
        if (DB::table('vocabulary_sets')->where('id', $vocabulary_sets_id)->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Xóa bộ từ thành công'
            ]);
        }
    }
}
