<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Exception;
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
            'vocabularies.definition_image'
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
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
        $request->validate([
            'word' => 'required|unique:vocabularies|max:191',
            'definition' => 'required|max:255',
            'word_lang' => 'required|max:255',
            'def_lang' => 'required|max:255',
            'definition_image' => 'required|max:255',
        ]);
        $vocabularies_id = DB::table('vocabularies')->insertGetId([
            'word' => $request->get('word'),
            'definition' => $request->get('definition'),
            'word_lang' => $request->get('word_lang'),
            'def_lang' => $request->get('def_lang'),
            'definition_image' => $request->get('definition_image')
        ]);
        if (empty($vocabularies_id)) {
            return response()->json([
                'success' => true,
                'message' => 'Đã có lỗi xảy ra, thất bại'
            ], 200);
        }
        return response()->json([
            'success' => true,
            'message' => 'Thành công'
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $id = $request->get('vocabularies_id');
        $isUpdate = DB::table('vocabularies')->where('id',$id)->update($request->all());
        if($isUpdate){
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật hành công'
            ], 200);
        }
        return response()->json([
            'success' => true,
            'message' => 'Không thành công'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //
        $vocabularies_id = $request->get('vocabularies_id');
        if (empty($vocabularies_id)) {
            return response()->json([
                'success' => true,
                'message' => 'Không tìm thấy từ'
            ], 200);
        }
        if (DB::table('vocabularies')->where('id', $vocabularies_id)->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Xóa thành công'
            ], 200);
        }
    }
}
