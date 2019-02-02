<?php
class MainController extends BaseController
{
    public function actionIndex()
    {
        $this->url="index";
        $this->title="首页";
        
        $sort=strtolower(@arg("sort"));
        $keyword=strtolower(@arg("keyword"));
        $page=@arg("page");
        $filter=strtolower(@arg('filter'));
        $this->args=[
            'sort' => $sort,
            'keyword' => $keyword,
            'page' => $page,
            'filter' => $filter,
        ];

        $keyword=str_replace("[","[[]",$keyword);
        $keyword=str_replace("_","[_]",$keyword);
        $keyword=str_replace("%","[%]",$keyword);
        $conditions[':keyword']='%'.$keyword.'%';
        $conditions[0]=" name like :keyword ";
        switch($filter){
            case 'borrowable':
                $conditions[0]=$conditions[0].'AND scode = :scode';
                $conditions[':scode']='1';
                $filter='AND scode = 1';
                break;
            case 'soldout':
                $conditions[0]=$conditions[0].'AND scode = :scode';
                $conditions[':scode']='0'; // 0是无货 ， -1 是 下架
                $filter='AND scode = 0';
                break;
            // case 'credit':
            //     $conditions[0]
            //TODO 貌似又是个跨表查询,等后期再实现吧
            case 'mine':
                $conditions[0]=$conditions[0].'AND owner = :owner';
                $conditions[':owner']=$this->userinfo['uid'];
                $filter='AND owner = '.$this->userinfo['uid'];
                break;
            default:
                $conditions[0]=$conditions[0].'AND scode > 0';
                $filter='AND scode > 0';
        }

        $items = new Model("item");
        //TODO 2   关于是否展示已出借完的物品，后期再讨论讨论吧
        if($sort==="bycount"){
            $items_res=$items->findAll($conditions,'order_count DESC',"*",array($page,8,6));
        }
        else if($sort==="bycredit"){
            $keyword="'%".$keyword."%'";
            $items_res=$items->query("select item.*,users.credit from item join users on item.owner=users.uid where item.name like $keyword $filter ORDER BY users.credit DESC;");
            $page=max(1,$page);
            $items->pager($page,8,6,count($items_res));
            if(!empty($items->page)){
                $items_res=array_slice($items_res,($page-1)*6,6,true);
            }
        }
        else{
            $items_res=$items->findAll($conditions,'create_time DESC',"*",array($page,8,6));
            $this->args['sort']='default';
        }

        $user=new Model("users"); //显示发布者
        for($i = 0;$i < count($items_res);$i++) {
            $user_res=$user->find(array("uid=:uid",":uid" => $items_res[$i]["owner"]));
            $items_res[$i]['publisher_real_name'] = $user_res['real_name'];
        }

        $this->pager=$items->page;
        $this->items_info=$items_res;
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