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
        $request->validate([
            'added_vocabularies' => 'required',
            "def_lang" => 'required',
            "word_lang" => 'required',
            'vocabulary_set_id' => 'required',
            'created_user_id' => 'required|max:255',
        ]);
        $listWords = $request->input('added_vocabularies');
        foreach ($listWords as $word){
            $vocabularies_id = DB::table('vocabularies')->insertGetId([
                'word' => $word['word'],
                'definition' => $word['definition'],
                'definition_image' => $word['definition_image'],
                "def_lang" => $request->input('def_lang'),
                "word_lang" => $request->input('word_lang'),
                'vocabulary_set_id' => $request->input('vocabulary_set_id'),
                'created_user_id' => $request->input('created_user_id'),
            ]);
        }

        if (empty($vocabularies_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra, thất bại!'
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Thêm từ vựng thành công!'
        ]);
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
