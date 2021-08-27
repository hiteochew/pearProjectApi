<?php

/**
 * Created by PhpStorm.
 * SystemUser: vilson
 * Date: 2018/7/10
 * Time: 12:38
 */

namespace app\project\behavior;


use app\common\Model\CommonModel;
use app\common\Model\Member;
use app\common\Model\ProjectLog;
use service\MessageService;
use think\facade\Log;

class File
{
    /**
     * 任务操作钩子
     * @param $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function run($data)
    {
        $logData = ['member_code' => $data['memberCode'], 'source_code' => $data['sourceCode'], 'project_code' => $data['projectCode'], 'remark' => $data['remark'], 'type' => $data['type'], 'content' => $data['content'], 'is_comment' => $data['isComment'], 'to_member_code' => $data['toMemberCode'], 'create_time' => nowTime(), 'code' => createUniqueCode('projectLog'), 'file_code' => $data['fileCode'], 'action_type' => 'file'];

        $toMember = [];
        if ($data['toMemberCode']) {
            $toMember = Member::where(['code' => $data['toMemberCode']])->find();
        }
        $notifyData = [
            'title' => '',
            'content' => '',
            'type' => '',
            'action' => '',
            'terminal' => '',
        ];
        $remark = '';
        $content = '';
        switch ($data['type']) {
            case 'create':
                $icon = 'plus';
                $remark = '上传了文件 ';
                $content = $data['data']['fullName'];
                $notifyData['title'] = "";
                $notifyData['action'] = "";
                break;
            case 'edit':
                $icon = 'edit';
                $remark = '编辑了文件 ';
                $content = $data['data']['fullName'];
                break;
            case 'name':
                $icon = 'edit';
                $remark = '修改了文件名 ';
                $content = $data['data']['fullName'];
                break;
            case 'recycle':
                $icon = 'delete';
                $remark = '把文件移到了回收站 ';
                break;
            case 'recycledel':
                $icon = 'delete';
                $remark = '把文件从回收站彻底删除了 ';
                break;
            case 'recovery':
                $icon = 'undo';
                $remark = '恢复了文件 ';
                break;
            case 'uploadFile':
                $icon = 'link';
                $remark = '上传了文件文件 ';
                $content = "<a target=\"_blank\" class=\"muted\" href=\"{$data['data']['file_url']} \">{$data['data']['fullName']}</a>";

                break;
            case 'deleteFile':
                $icon = 'disconnect';
                $remark = '删除了文件 ';
                $content = "<a target=\"_blank\" class=\"muted\" href=\"{$data['data']['file_url']} \">{$data['data']['fullName']}</a>";
                break;
            default:
                $icon = 'plus';
                $remark = ' 创建 ';
                break;
        }
        $logData['icon'] = $icon;
        if (!$data['remark']) {
            $logData['remark'] = $remark;
        }
        if (!$data['content']) {
            $logData['content'] = $content;
        }
        ProjectLog::create($logData);
        if (false) {
            //todo 短信,消息推送
            $notifyModel = new \app\common\Model\Notify();
            $notifyData['content'] = "";
            $result = $notifyModel->add($notifyData['title'], $notifyData['content'], $notifyData['type'], 0, 0, $notifyData['action'], json_encode($data['data']), $notifyData['terminal']);
            $organizationCode = getCurrentOrganizationCode();
            $messageService = new MessageService();
            $messageService->sendToAll(['content' => $notifyData['content'], 'title' => $notifyData['title'], 'data' => ['organizationCode' => $organizationCode], 'notify' => $result], $notifyData['action']);
        }
    }
}
