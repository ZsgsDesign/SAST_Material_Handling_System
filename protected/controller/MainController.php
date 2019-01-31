<?php
class MainController extends BaseController
{
    public function actionIndex()
    {
        $this->url="index";
        $this->title="首页";
        $debug=@arg("debug");//TODO1   当传入debug时，展示的时测试的静态的内容，后期记得删除
        if(!$debug){
            $sort=strtolower(@arg("sort"));
            $keyword=strtolower(@arg("keyword"));
            $page=@arg("page");
            if(!empty($keyword)){
                $keyword=str_replace("[","[[]",$keyword);
                $keyword=str_replace("_","[_]",$keyword);
                $keyword=str_replace("%","[%]",$keyword);
            };
            $keyword="'%".$keyword."%'";
            // $queryArray=array();
            // if(!empty($keyword)){
            //     $queryArray=[
            //         "name like :keyword",
            //         ":keyword" => $keyword,
            //     ];
            // }
            $items = new Model("item");
            //TODO2   关于是否展示已出借完的物品，后期再讨论讨论吧
            //TODO3    底下的查询用的是字符串拼接，很不好，网站容易被黑，主要是findAll函数的condition参数怪怪的，后面再改
            if($sort==="bycount"){
                $items_res=$items->query("select * from item where name like $keyword order by order_count DESC");
            }
            else if($sort==="bycredit"){
                $items_res=$items->query("select * from item where name like $keyword order by create_time DESC");
                //TODO4   这个需要跨表查询，先用credit_limit将就着，后期再改
            }
            else{
                $items_res=$items->query("select * from item where name like $keyword order by create_time DESC");
            }
            if(!empty($items_res)){
                $pageSize=10;
                $page=intval($page);
                $pageScope=ceil(count($items_res)/$pageSize);
                $page=min(max(1,$page),$pageScope);
                $items_res=array_slice($items_res,($page-1)*$pageSize,$pageSize);
            }//此处是实现分页。当page的值为非正整数或为空时，page的值为1,当page的值超出范围时,显示最后一页
            //TODO5   主要是框架的pager()不太会用,所以没有去用。这个和TODO3一起改
            $this->items_info=$items_res;
        }
        else{
            $this->items_info=array(
                array(
                    "name" => "金士顿（Kingston）64GB USB3.0 U盘 DTSE9G2 银色 金属外壳 高速读写",
                    "dec" => "一个U盘",
                    "order_count" => "200",
                ),
                array(
                    "name" => "闪迪 （SanDisk）64GB USB3.0 U盘 CZ73酷铄 银色 读速150MB/s 金属外壳 内含安全加密软件",
                    "dec" => "一个U盘",
                    "order_count" => "200",
                )
            );
        };
    }

    public function actionDetail()
    {
        $this->url="detail";
        $this->title="物品详情";
    }

    public function actionCart()
    {
        $this->url="cart";
        $this->title="购物车";
    }

    public function actionMhs()
    {
        $this->jump("{$this->MHS_DOMAIN}/");
    }

    public function actionAccount()
    {
        $this->jump("{$this->MHS_DOMAIN}/account/");
    }
}