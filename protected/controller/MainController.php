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
            $keyword="'%".$keyword."%'";
            $items_res=$items->query("select item.*,users.credit from item join users on item.owner=users.uid where item.name like $keyword ORDER BY users.credit DESC;");
            $page=max(1,$page);
            $items->pager($page,6,6,count($items_res));
            if(!empty($items->page)){
                $items_res=array_slice($items_res,($page-1)*6,6,true);
            }
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
        $cart=new Model("cart");
        $item=new Model("item");
        $users=new Model("users");
        $cart_res=$cart->query("SELECT a.*,users.real_name FROM(select cart.*,item.`name`,item.`owner` FROM cart JOIN item on cart.item_id=item.iid) AS a JOIN users ON a.`owner`=users.uid WHERE `user`=".$this->userinfo['uid'].";");
        $this->cart_items=$cart_res;
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