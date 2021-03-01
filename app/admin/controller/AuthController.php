<?php


namespace app\admin\controller;


use app\BaseController;
use app\blog\controller\BlogController;
use app\blog\model\BlogAdminModel;
use think\facade\Cookie;
use think\facade\Session;
use think\Request;

class AuthController extends BaseController
{

    public function data_center_login(){
        echo 222;exit;
        return view();
    }


    public function login_in(Request $request){
        $data= $request->post('');
        //账号检测
        $res = BlogAdminModel::blog_account_search(['admin_account'=>$data['admin_account']]);
        if($res && $res['admin_status']==1){
            //密码检测
            if(md5(md5($data['admin_pwd']).$res['admin_splicing_character'])==$res['admin_pwd']){
                //验证码检测
                if( !captcha_check($data['code'])){
                    //验证码错误
                    BlogController::return_error("请输入正确的验证码！");return;
                }else{
                    //获取ip地址和登陆时间
                    $update=[
                        'admin_login_ip'=>$_SERVER["REMOTE_ADDR"],
                        'admin_login_time'=>date("Y-m-d H:i:s",time()),
                    ];
                    $where=['id'=>$res['id']];
                    //修改管理登陆信息
                    $re_update =BlogAdminModel::admin_login_update($where,$update);
                    if($re_update==true){
                        $arr = [
                            "admin_id"      => $res['id'],
                            "admin_account" => $res["admin_account"],
                            "admin_name"    => $res["admin_name"]
                        ];
                        if (isset($data['check']) && $data['check'] == 1){
                            Cookie::set("blog_admin", $arr, 7 * 24 * 3600);
                            Session::set("blog_admin", $arr);
                        }else{
                            Cookie::set("blog_admin", $arr);
                            Session::set("blog_admin", $arr);
                        }
                        BlogController::return_success("登陆成功！欢迎您回家！");return;
                    }else{
                        BlogController::return_error($re_update);return;
                    }
                }
            }else{
                BlogController::return_error("密码错误！请重新输入！");return;
            }
        }
        if($res && $res['admin_status'==2]){
            BlogController::return_error("该账号异常，请务使用！");return;
        }
        if(!$res){
            BlogController::return_error("对不起，管理员账号异常，请填写正确账号！");return;
        }
    }

    //退出
    public function login_out(){
        if(Cookie::get("blog_admin")){
            Cookie::delete("blog_admin");
        }
        Session::delete("blog_admin");
        BlogController::return_success('记得常回家看看！');
    }
}