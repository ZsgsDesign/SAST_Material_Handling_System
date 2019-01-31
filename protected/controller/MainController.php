<?php
class MainController extends BaseController
{
    public function actionIndex()
    {
        $this->url="index";
        $this->title="首页";
        
        $sort=strtolower(@arg("sort"));
        $keyword=strtolower(@arg("keyword"));
        $page=arg("page");
        $this->args=[
            'sort' => $sort,
            'keyword' => $keyword,
            'page' => $page,
        ];
        dump($this->args);
        if(!empty($keyword)){
            $keyword=str_replace("[","[[]",$keyword);
            $keyword=str_replace("_","[_]",$keyword);
            $keyword=str_replace("%","[%]",$keyword);
            $conditions=[
                "name like :keyword",
                ":keyword" => '%'.$keyword.'%',
            ];
        }
        else{
            $conditions=null;
        }
        $items = new Model("item");
        //TODO2   关于是否展示已出借完的物品，后期再讨论讨论吧
        if($sort==="bycount"){
            $items_res=$items->findAll($conditions,'order_count DESC',"*",array($page,6,6));
        }
        else if($sort==="bycredit"){
            $items_res=$items->findAll($conditions,'credit_limit DESC',"*",array($page,6,6));
            //TODO4   这个需要跨表查询，先用credit_limit将就着，后期再改
        }
        else{
            $items_res=$items->findAll($conditions,'create_time DESC',"*",array($page,6,6));
            $this->args['sort']='default';
        }
        $this->pager=$items->page;
        $this->items_info=$items_res;
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