<?php

namespace App\Console\Commands;

use App\Models\UserPorait;
use Illuminate\Console\Command;

class UserPortraitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspire:user_portrait';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    protected $code_weight;
    protected $view_weight;
    protected $collect_weight;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->code_weight = env('CODE_WEIGHT');
        $this->view_weight = env('VIEW_WEIGHT');
        $this->collect_weight = env('LIKE_WEIGHT');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $collectInfo = UserPorait::getFavoritesInfo();
        $codeInfo = UserPorait::getCodeInfo();
        $viewInfo = UserPorait::getViewInfo();
        $dataValues = [
            'collect_info'=>$collectInfo,
            'code_info'=>$codeInfo,
            'view_info'=>$viewInfo
        ];
        // 计算每个用户的偏好得分
        $preference_scores = [];
        foreach ($dataValues as $key=>$value) {
            foreach ($value as $item) {
                $product_id = $item->product_id;
                $reviewer_id = $item->reviewer_id;
                if (!isset($preference_scores[$reviewer_id])) {
                    $preference_scores[$reviewer_id] = [];
                }
                if (!isset($preference_scores[$reviewer_id][$product_id])) {
                    $preference_scores[$reviewer_id][$product_id] = 0;
                }
                if($key == "collect_info"){
                    $preference_scores[$reviewer_id][$product_id] += $this->collect_weight * $item->total_collect;
                }else if($key == "code_info"){
                    $preference_scores[$reviewer_id][$product_id] += $this->code_weight * $item->total_vouchers;
                }else if($key == "view_info"){
                    $preference_scores[$reviewer_id][$product_id] += $this->view_weight * $item->total_view;
                }
            }
        }

        // 输出结果
        $scoreArray = [];
        foreach ($preference_scores as $user => $scores) {
            $ans = 0; //每个用户只取前5个
            foreach ($scores as $product => $score) {
                if($ans>=5){
                    continue;
                }
                if (!isset($scoreArray[$user])) {
                    $scoreArray[$user] = [];
                }
                array_push($scoreArray[$user], ['user_id'=>$user,'product_id' => $product , 'score' => round($score*100, 2),'created_at'=> date('Y-m-d H:i:s',time())]);
                $ans++;
            }
        }

        //添加二级类目名称
        $productIds = [];
        foreach ($scoreArray as $scores) {
            foreach ($scores as $score) {
                $productIds[] = $score['product_id'];
            }
        }
        //分批执行获取类目
//       // TODO 分批执行
        $batchSize = 1000; // 每个批次的大小
        $batches = ceil(count($productIds) / $batchSize); // 总批次数
        $productGroups = []; // 存储查询结果的数组
        for ($i = 0; $i < $batches; $i++) {
            $start = $i * $batchSize;
            $end = $start + $batchSize;
            $batchIds = array_slice($productIds, $start, $end - $start);
            // 查询数据库并将结果存储到数组中
            $batchResults  = UserPorait::batchSelectGroup($batchIds);
            $productGroups = $productGroups+$batchResults;
        }

        foreach ($scoreArray as &$scores) {
            foreach ($scores as &$score) {
                $productId = $score['product_id'];
                if (isset($productGroups[$productId])) {
                    $score['product_group'] = $productGroups[$productId];
                }
            }
        }
        //保存到数据库（只保留最新的）
        foreach ($scoreArray as $userId => $products) {
            foreach ($products as $product) {
                UserPorait::insertUserInfo($product);
            }
        }
        echo 'success';
    }
}
