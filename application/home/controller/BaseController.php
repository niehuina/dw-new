<?php


namespace app\home\controller;

use app\home\model\Notification;
use app\home\model\PartyMember;
use app\home\model\Setting;
use app\home\common\Constant;
use app\home\model\Link;
use app\home\model\Section;
use app\home\model\WebUser;
use think\Controller;

class BaseController extends Controller
{
    /**
     * $isPM 是否是党员
     * $isPT 是否是检察官
     */
    var $userId, $user, $pm_organ, $isPM, $isPT;
    var $page_length = 10;

    protected function _initialize()
    {
        $controller_name = strtolower($this->request->controller());
        $action_name = strtolower($this->request->action());

        $section_list = Section::get_list(['tf_show_index'=>1,'parent_id'=>0],'id,name,tf_show_index,url', 'sort asc', 0, 9);
        $this->assign('section_list',$section_list);

        $link_list = Link::get_list([],'id,name,redirect_url');
        $this->assign('link_list',$link_list);

        $website = Setting::get_list('website','code');
        $this->assign('website',$website);

        $tf_party_member=false;
        $login_user_id = session(Constant::SESSION_USER_ID);
        $party_member = PartyMember::get(['web_user_id' => $login_user_id]);
        if($party_member){
            $tf_party_member=true;
        }

        $this->isPT = false;

        $this->assign('tf_party_member',$tf_party_member);

        if (session(Constant::SESSION_USER_ID)) {
            $user_id = session(Constant::SESSION_USER_ID);
            $user = WebUser::get(['id' => $user_id, 'active' => 1, 'deleted' => 0]);
            if (!empty($user)) {
                $this->user = $user;
                $this->userId = $user_id;
                $this->assign("user", $this->user);

                $user_type = Setting::get($user->user_type);
                if($user_type && $user_type->code == 'prosecutor'){
                    $this->isPT = true;
                }
                $this->assign('tf_prosecutor',$this->isPT);

                $count = Notification::get_count(['web_user_id'=>$user_id]);
                $this->assign('message_count',$count);
            }
        }else if($controller_name=='user'){
            unset($this->user);
            unset($this->userId);
            $this->redirect(url('/home/index'));
        }
    }
}