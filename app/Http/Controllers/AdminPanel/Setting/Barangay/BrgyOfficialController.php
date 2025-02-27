<?php

namespace App\Http\Controllers\AdminPanel\Setting\Barangay;

use App\Http\Controllers\Controller;
use App\Models\brgy_official;
use App\Models\area_setting;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\Barangayimage;


class BrgyOfficialController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $Barangayimage = DB::table('barangayimages')
        ->where('barangay_id','=','1')->first();
        $official_empty = brgy_official::orderBy('official_id')
                ->limit(1)
                ->get();

                $area = area_setting::orderBy('area_id', 'desc')->get();

                if ($request->ajax()) {
                    $data = area_setting::orderBy('area_id', 'desc')->get();
                    return Datatables::of($data)
                          ->addIndexColumn()
                            ->addColumn('action', function($row){

                                   $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->area_id.'" data-original-title="trash" class=" btn btn-danger btn-xs pr-4 pl-4 trash"  ><i class="fa fa-trash fa-lg"></i> </a>';


                                    return $btn;
                            })
                            ->rawColumns(['action'])
                            ->make(true);
                }
                return view('pages.setting.maintenance',[compact('area'),'Barangayimage'=>$Barangayimage,
                'official_empty'=>$official_empty]);



    }

    public function barangay(Request $request)
    {

        $data = DB::table('brgy_officials')->get();
        if ($request->ajax()) {
            $data = DB::table('brgy_officials')->get();

            return Datatables::of($data)
                  ->addIndexColumn()
                    ->addColumn('action', function($row){

                        $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->official_id.'" data-original-title="Edit" class="editbarangay btn btn-info  btn-xs pr-4 pl-4 "><i class="fa fa-pencil fa-lg"></i> </a>';
                        $btn = $btn.' <a href="javascript:void(0)" data-toggle="tooltip"   data-id="'.$row->official_id.'" data-original-title="Delete" class="btn btn-danger btn-xs pr-4 pl-4 deletebarangay"><i class="fa fa-trash fa-lg"></i> </a>';


                            return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }


    }


    public function barangayPOST(Request $request){

        $barangay = DB::table('brgy_officials')->count();
        if($barangay <= 10){
        brgy_official::updateOrCreate(['official_id' => $request->official_id],
        ['name' => $request->name,
        'position' => $request->position,
        'official_committe' => $request->official_committe,
        'year_of_service' =>$request->year_of_service]);
        return response()->json(['success'=>'Official saved successfully.']);
        }else{


            return response()->json(['Failed'=>'No more than 11 member.']);
        }


    }





    public function store(Request $request)
    {


        // area setting
        if (area_setting::where('area', '=', $request->area)->exists()) {
            return response()->json(['Duplicate'=>'Area Not Saved.']);
         }else{
            area_setting::updateOrCreate(['area_id' => $request->area_id],
            ['area' => $request->area]);
            return response()->json(['success'=>'Area saved successfully.']);
         }
         //count the number of resident living on the area
         $data = DB::table('area_settings')
         ->select('area')->get();

         if(count($data))
          foreach ($data as $data) {
              $test = DB::table('resident_infos')
              ->where('area','=',$data->area)->count();
              area_setting::where('area', '=', $data->area)
             ->update(['population' => $test]);
          }



    }


    public function barangayedit($id)
    {
        $barangay_official = brgy_official::find($id);
        return response()->json($barangay_official);
    }


    public function barangaydelete($id)
    {
       brgy_official::find($id)->delete();
        return response()->json(['success'=>'Delete successfully.']);
    }
    public function destroy(Request $request,$id)
    {

        area_setting::find($id)->delete();
        return response()->json(['success'=>'Delete successfully.']);

    }










}
