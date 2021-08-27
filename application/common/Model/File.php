<?php

namespace app\common\Model;

use function GuzzleHttp\Promise\task;
use think\Db;
use think\facade\Hook;

/**
 * 文件
 * Class TaskStar
 * @package app\common\Model
 */
class File extends CommonModel
{
    protected $append = ['fullName'];

    /**
     * @param $projectCode
     * @param string $taskCode
     * @param $data
     * @return File
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function createFile($projectCode, $data)
    {
        if ($projectCode) {
            $project = Project::where(['code' => $projectCode])->find();
            if (!$project) {
                throw new \Exception('该项目已失效', 1);
            }
        }
        $memberCode = getCurrentMember()['code'];
        $orgCode = getCurrentOrganizationCode();
        $fileData = [
            'code' => createUniqueCode('file'),
            'create_by' => $memberCode,
            'project_code' => $projectCode ? $projectCode : '',
            'organization_code' => $orgCode,
            'path_name' => isset($data['path_name']) ? $data['path_name'] : '',
            'title' => isset($data['title']) ? $data['title'] : '',
            'extension' => isset($data['extension']) ? $data['extension'] : '',
            'size' => isset($data['size']) ? $data['size'] : '',
            'object_type' => isset($data['object_type']) ? $data['object_type'] : '',
            'extra' => isset($data['extra']) ? $data['extra'] : '',
            'file_url' => isset($data['file_url']) ? $data['file_url'] : '',
            'file_type' => isset($data['file_type']) ? $data['file_type'] : '',
            'create_time' => nowTime(),
        ];
        $result = self::create($fileData);
        return $result;
    }

    /**
     * 放入回收站
     * @param $code
     * @return File
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function recycle($code)
    {
        $info = self::where(['code' => $code])->find();
        if (!$info) {
            throw new \Exception('文件不存在', 1);
        }
        if ($info['deleted']) {
            throw new \Exception('文件已在回收站', 2);
        }
        $result = self::update(['deleted' => 1, 'deleted_time' => nowTime()], ['code' => $code]);
        return $result;
    }

    /**
     * 恢复文件
     * @param $code
     * @return File
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function recovery($code)
    {
        $info = self::where(['code' => $code])->find();
        if (!$info) {
            throw new \Exception('文件不存在', 1);
        }
        if (!$info['deleted']) {
            throw new \Exception('文件已恢复', 2);
        }
        $result = self::update(['deleted' => 0], ['code' => $code]);
        return $result;
    }

    public function deleteFile($code)
    {
        //todo 权限判断
        $info = self::where(['code' => $code])->find();
        if (!$info) {
            throw new \Exception('文件不存在', 1);
        }
        Db::startTrans();
        try {
            self::where(['code' => $code])->delete();
            //todo 删除物理文件
            self::_delfile($info['path_name']);
            Db::commit();
            self::fileHook(getCurrentMember()['code'], $info['task_code'], $info['project_code'], 'deleteFile', '', 0, '', '', $code, $info);
        } catch (\Exception $e) {
            Db::rollback();
            throw new \Exception($e->getMessage());
        }
        return true;
    }

    public function getFullNameAttr($value, $data)
    {
        return "{$data['title']}.{$data['extension']}";
    }

    /** 文件变动钩子
     * @param $memberCode
     * @param $sourceCode
     * @param string $type
     * @param string $toMemberCode
     * @param int $isComment
     * @param string $remark
     * @param string $content
     * @param string $fileCode
     * @param array $data
     * @param string $tag
     */
    public static function fileHook($memberCode, $sourceCode = '', $projectCode = '', $type = 'create', $toMemberCode = '', $isComment = 0, $remark = '', $content = '', $fileCode = '', $data = [], $tag = 'file')
    {
        $data = ['memberCode' => $memberCode, 'sourceCode' => $sourceCode, 'projectCode' => $projectCode, 'remark' => $remark, 'type' => $type, 'content' => $content, 'isComment' => $isComment, 'toMemberCode' => $toMemberCode, 'fileCode' => $fileCode, 'data' => $data, 'tag' => $tag];
        Hook::listen($tag, $data);
    }
}
