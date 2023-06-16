<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;


class UserPorait extends Model
{
    //获取领码行为数据
    public static function getCodeInfo(){
        $results = DB::connection('mysql')->table('tbl_request')
            ->select('reviewer_id', 'product_id', DB::raw('COUNT(*) AS total_vouchers'), DB::raw('MAX(`timestamp`) AS last_voucher_date'))
            ->where('timestamp', '>=', Carbon::now()->subDays(90))
            ->groupBy('reviewer_id', 'product_id')
            ->orderByDesc('total_vouchers')
            ->orderByDesc('last_voucher_date')
            ->limit(10)
            ->get()
            ->toArray();
        return $results;
    }

    //获取收藏行为数据
    public static function getFavoritesInfo(){
        $results = DB::connection('mysql_vipon_event')->table('tbl_product_favorites_event')
            ->select('reviewer_id', 'product_id', DB::raw('COUNT(*) AS total_collect'), DB::raw('MAX(`createtime`) AS last_collect_date'))
            ->whereRaw("createtime >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 90 DAY))")
            ->groupBy('reviewer_id', 'product_id')
            ->orderByDesc('total_collect')
            ->orderByDesc('last_collect_date')
            ->limit(10)
            ->get()
            ->toArray();
            return $results;
    }

    //获取浏览行为数据
    public static function getViewInfo(){
        $results = DB::connection('mysql_vipon_event')->table('tbl_product_click_event')
            ->select('reviewer_id', 'product_id', DB::raw('COUNT(*) AS total_view'), DB::raw('MAX(`createtime`) AS last_view_date'))
            ->whereRaw("createtime >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 14 DAY))")
            ->groupBy('reviewer_id', 'product_id')
            ->orderByDesc('total_view')
            ->orderByDesc('last_view_date')
            ->limit(10)
            ->get()
            ->toArray();
        return $results;
    }

    //保存获取到的数据
    public static function insertUserInfo($array){
        DB::connection('mysql_vipon_event_test')->table('tbl_product_portrait')
            ->updateOrInsert(['user_id'=>$array['user_id'],'product_id'=>$array['product_id'],'score'=>$array['score']],$array);
    }

    /**
     * @param $product_ids
     * @return void 批量查询产品类目
     */
    public static function batchSelectGroup($product_ids):array{
        $productGroups = DB::table('tbl_product_art')
            ->whereIn('product_id', $product_ids)
            ->pluck('art_product_group', 'product_id')
            ->toArray();
        return $productGroups;
    }

    //根据productId查询二级类目
    public static function getProductType($productId){
        $product_group = DB::connection('mysql')->table('tbl_product_art')
            ->select('art_product_group')
            ->where('product_id', '=', $productId)
            ->limit(1)
            ->get()
            ->toArray();
        return $product_group[0]->art_product_group??'';
    }

    /**
     * @param $page
     * @param $page_size
     * @return \Illuminate\Support\Collection
     * 全部用户分页接口
     */
    public static function getPageUserScore($page,$page_size){
        $userList = DB::connection('mysql_vipon_event_test')->table('tbl_product_portrait')
            ->select('user_id','product_id','product_group','score')
            ->offset(($page-1)*$page_size)
            ->orderBy('user_id','desc')
            ->limit($page_size)
            ->get();
        return $userList;
    }

    /**
     * @param $page
     * @param $page_size
     * @return \Illuminate\Support\Collection
     * 用户分页接口
     */
    public static function getPageUserScoreByUserId($page,$page_size,$user_id){
        $userList = DB::connection('mysql_vipon_event_test')->table('tbl_product_portrait')
            ->select('user_id','product_id','product_group','score')
            ->offset(($page-1)*$page_size)
            ->where('user_id',$user_id)
            ->orderBy('user_id','desc')
            ->limit($page_size)
            ->get();
        return $userList;
    }


}
