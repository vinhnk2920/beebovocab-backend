<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VocabulariesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $field = [
            'vocabularies.word',
            'vocabularies.definition',
            'vocabularies.word_lang',
            'vocabularies.def_lang',
            'vocabularies.definition_image',
            'vocabularies.vocabulary_set_id',
            'vocabularies.created_user_id',
        ];
        $vocabularies = DB::table('vocabularies')->get($field);
        if (empty($vocabularies)) {
            return response()->json([
                'success' => true,
                'messeage' => 'Không có dữ liệu'
            ], 200);
        }
        return response()->json([
            'success' => true,
            'data' => [
                'vocabularies' => $vocabularies,
            ],
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        //
        $request->validate([
            'word' => 'required|max:191',
            'definition' => 'required|max:255',
            'word_lang' => 'required|max:255',
            'def_lang' => 'required|max:255',
            'definition_image' => 'required|max:255',
            'vocabulary_set_id' => 'required',
            'created_user_id' => 'required|max:255',
        ]);

        $vocabularies_id = DB::table('vocabularies')->insertGetId([
            'word' => $request->get('word'),
            'definition' => $request->get('definition'),
            'word_lang' => $request->get('word_lang'),
            'def_lang' => $request->get('def_lang'),
            'definition_image' => $request->get('definition_image'),
            'vocabulary_set_id' => $request->get('vocabulary_set_id'),
            'created_user_id' => $request->get('created_user_id')
        ]);

        if (empty($vocabularies_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra, thất bại!'
            ], 200);
        }
        return response()->json([
            'success' => true,
            'message' => 'Thêm từ vựng thành công!'
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = $request->get('id');
        if (is_null($id)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần nhập vào từ vựng!'
            ]);
        }
        $vocab =  DB::table('vocabularies')->where('id',$id)->get()->toArray();
        if (empty($vocab)) {
            return response()->json([
                'success' => false,
                'message' => 'Không tồn tại từ vựng cần chỉnh sửa!'
            ]);
        }
        $isUpdate = DB::table('vocabularies')->where('id',$id)->update($request->all());
        if($isUpdate){
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật hành công!'
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Cập nhật không thành công'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        //
        $vocabularies_id = $request->get('vocabularies_id');
        if (empty($vocabularies_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy từ vựng cần xóa!'
            ]);
        }
        if (DB::table('vocabularies')->where('id', $vocabularies_id)->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Xóa từ vựng thành công!'
            ]);
        }
    }
}
