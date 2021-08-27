<?php

namespace app\project\controller;

use app\common\Model\Member;
use controller\BasicApi;
use service\FileService;
use think\Exception;
use think\facade\Request;


/**
 */
class Lab extends BasicApi
{
    public function __construct()
    {
        /* parent::__construct();
        if (!$this->model) {
            $this->model = new \app\common\Model\File();
        } */
    }

    /**
     * 显示资源列表
     * @return void
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $this->success('', ['实验室']);
    }
}
