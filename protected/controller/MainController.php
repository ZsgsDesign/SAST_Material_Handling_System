<?php
class MainController extends BaseController
{
    public function actionIndex()
    {
        $this->url="index";
        $this->title="首页";
        
        $sort=strtolower(@arg("sort"));
        $this->keyword = $keyword=strtolower(@arg("keyword"));
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
                $conditions[0]=" $conditions[0] AND scode = :scode AND credit_limit <= :userCredit ";
                $conditions[':scode']='1';
                $conditions[':userCredit']=$this->userinfo['credit'];
                $filter=" AND scode = ".$conditions[':scode']." AND credit_limit <= ".$conditions[':userCredit']." ";
                break;
            case 'soldout':
                $conditions[0]=" $conditions[0] AND scode = :scode ";
                $conditions[':scode']='0'; // 0是无货 ， -1 是 下架
                $filter='AND scode = '.$conditions[':scode'].' ';
                break;
            case 'credit':
                $conditions[0]=" $conditions[0] AND credit_limit > :userCredit ";
                $conditions[':userCredit']=$this->userinfo['credit'];
                $filter=" AND credit_limit > ".$conditions[':userCredit'];
            case 'mine':
                $conditions[0]=$conditions[0].'AND owner = :owner ';
                $conditions[':owner']=$this->userinfo['uid'];
                $filter='AND owner = '.$this->userinfo['uid'];
                break;
            default:
                $conditions[0]=$conditions[0].'AND scode > 0';
                $filter=' AND scode > 0 ';
        }

        $items = new Model("item");
        //TODO 2   关于是否展示已出借完的物品，后期再讨论讨论吧
        //建议不展示，因为筛选中有显示无货功能
        if($sort==="bycount"){
            $items_res=$items->findAll($conditions,'order_count DESC',"*",array($page,8,6));
        }
        else if($sort==="bycredit"){
            $keyword="'%".$keyword."%'";
            $items_res=$items->query("select item.*,users.credit from item join users on item.owner=users.uid where item.name like $keyword $filter ORDER BY users.credit DESC;");
            $page=max(1,$page);
            $items->pager($page,8,6,count($items_res));
            if(!empty($items->page)){
                $items_res=array_slice($items_res,($page-1)*8,8,false);
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
        if($this->islogin){
            $order=new Model('`order`');
            $order_res_ownerchecked=array_column($order->query("SELECT `order`.oid,`order`.owner_checked,`order`.renter_checked,`order`.renter_id,item.owner,item.iid FROM `order` JOIN item ON item.iid = `order`.`item_id` WHERE item.`owner` = ".$this->userinfo['uid']),'owner_checked');
            $order_res_ownerchecked_count=empty(@$order_res_ownerchecked)?[]:array_count_values(@$order_res_ownerchecked);
            dump($order_res_ownerchecked);
            $order_res_renterchecked=array_column($order->query("SELECT `order`.oid,`order`.owner_checked,`order`.renter_checked,`order`.renter_id,item.owner,item.iid FROM `order` JOIN item ON item.iid = `order`.`item_id` WHERE `order`.renter_id = ".$this->userinfo['uid']),'renter_checked');
            $order_res_renterchecked_count=empty(@$order_res_renterchecked)?[]:array_count_values(@$order_res_renterchecked);
            $checked_count_typeA=0+@$order_res_ownerchecked_count['1']+@$order_res_ownerchecked_count['2']+@$order_res_ownerchecked_count['3']+@$order_res_ownerchecked_count['5']+@$order_res_renterchecked_count['1']+@$order_res_renterchecked_count['2']+@$order_res_renterchecked_count['3']+@$order_res_renterchecked_count['5'];
            $checked_count_typeB=0+@$order_res_ownerchecked_count['6']+@$order_res_renterchecked_count['6'];
            $this->count_typeA=$checked_count_typeA;
            $this->count_typeB=$checked_count_typeB;
            dump($checked_count_typeA);
            dump($checked_count_typeB);
        }
    }

    public function actionCart()
    {
        $this->url="cart";
        $this->title="购物车";
        $cart=new Model("cart");
        $item=new Model("item");
        $users=new Model("users");
        $cart_res=$cart->query("SELECT a.*,users.real_name FROM(select cart.*,item.`name`,item.`owner`,item.scode,item.count as item_count FROM cart JOIN item on cart.item_id=item.iid) AS a JOIN users ON a.`owner`=users.uid WHERE `user`=".$this->userinfo['uid'].";");
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