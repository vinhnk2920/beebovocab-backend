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
    public function index($set_id)
    {
        if(empty($set_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn phải nhập id của bộ từ!'
            ]);
        }
        $vocabularies = DB::table('vocabularies')->where('vocabulary_set_id', $set_id)->join('related_question', 'vocabularies.id', '=', 'related_question.vocabulary_id')->select('vocabularies.*', 'related_question.question')->get();
        if (empty($vocabularies)) {
            return response()->json([
                'success' => false,
                'messeage' => 'Không có dữ liệu'
            ]);
        }
        $newVocabList = [];
        foreach ($vocabularies->toArray() as $vocab) {
            $dot = str_repeat('_ ', strlen($vocab->word));
            $vocab->test = str_replace($vocab->word, $dot, $vocab->question);
            array_push($newVocabList, $vocab);
        }
        return response()->json([
            'success' => true,
            'data' => [
                'vocabularies' => $newVocabList
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
                "phonetic" => $word['phonetic'],
                "audio" => $word['audio'],
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function review(Request $request)
    {
        $request->validate([
            'vocab_id' => 'required',
            'answer' => 'required',
            'user_id' => 'required',
        ]);

        $vocab_id = $request->get('vocab_id');

        if (is_null($vocab_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần nhập vào từ vựng!'
            ]);
        }

        $result =  DB::table('learning_results')->where('vocabulary_id',$vocab_id)->get()->toArray();
        if (empty($result)) {
            $addedResult = DB::table('learning_results')->insertGetId([
                'vocabulary_id' => $request->get('vocab_id'),
                'user_id' => $request->get('user_id'),
                'f_rate' => '1111',
                'level' => '1',
            ]);
            if (empty($addedResult)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đã có lỗi xảy ra, thất bại'
                ]);
            }
            return response()->json([
                'success' => true,
                'message' => 'Ôn tập thành công!'
            ]);
        } else {
            $f_measure = DB::table('learning_results')->where('vocabulary_id',$vocab_id)->select('f_rate')->get()->toArray();
            $added_rate = $request->get('answer') . $f_measure[0]->f_rate;
            if ($added_rate[3] or $added_rate[4]) {
                $new_rate = substr($added_rate, 0, 3). "1";
            } else {
                $new_rate = substr($added_rate, 0, 3). "0";
            }
            $f_rate = 3*$new_rate[0] + 2*$new_rate[1] + $new_rate[2] + $new_rate[3];
            if ($f_rate == 8) {
                $level = "1";
            } else if (5 <= $f_rate and $f_rate < 8) {
                $level = "2";
            } else if (3 <= $f_rate and $f_rate <= 4) {
                $level = "3";
            } else if (1 <= $f_rate and $f_rate <= 2) {
                $level = "4";
            } else if ($f_rate == 0 ) {
                $level = "5";
            }
            $now = date('Y-m-d H:i:s');
            $isUpdate = DB::table('learning_results')->where('vocabulary_id',$vocab_id)->update(['f_rate' => $new_rate, 'level' => $level, 'updated_at' => $now]);
            if($isUpdate){
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật hành công'
                ], 200);
            }
            return response()->json([
                'success' => false,
                'message' => 'Không thành công'
            ]);
        }
    }
}
