<?php

namespace app\admin\controller;

use app\Common\Controller\AdminBaseController;

class Article extends AdminBaseController {

    protected $mod;

    public function __construct() {
        parent::__construct();
        $this->mod = new \app\admin\model\articleModel();
        $this->assign('notes', $this->mod->notes);
        $category = new \app\admin\model\categoryModel();
        $type = $category->getField(['status' => 1], 'id,title', 'sort asc');
        $this->assign('type', $type);
    }

    public function index() {
        $page = input('page', 1);
        $pageSize = 5; //每页显示的数量
        $where = [];
        if (input('id')) {
            $where[] = ['id', '=', input('id')];
        }
        $list = $this->mod->getList($where, $page, $pageSize);
        $count = $this->mod->getCount($where);

        $pageparam = $this->mod->_pageparam();
        $Page = new \think\paginator\driver\Bootstrap($list, $pageSize, $page, $count, FALSE, $pageparam);
        $show = $Page->render();
        //dump($list);
        $this->assign('list', $list);
        $this->assign('pages', $show);
        return $this->adminTpl();
    }

    public function edit() {
        $id = input('id');
        $info = $this->mod->getOne($id);
        $this->assign('info', $info);
        if (IS_POST) { //如果是post请求
            $data = input('post.');
            unset($data['id']);
            //dump($data); die;
            //修复上传图片不存在bug
            $file = request()->file();
            if($file){
                $file = request()->file('img'); //图片上传
                if ($file) {
                    $file_path = \think\facade\Env::get('ROOT_PATH') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';
                    $img_path = DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
                    $img_info = $file->move($file_path);
                    if ($img_info) {
                        $data['img'] = $img_path . $img_info->getSaveName();
                    } else {
                        $this->error($file->getError());
                    }
                }
            }
            $dotPos = strpos($data['desc'],"。");
            $data['desc'] = substr($data['desc'], '0', $dotPos + 3);
            //dump($data); die;
            if ($id) { //更新数据
                $where['id'] = $id;
                $x = $this->mod->Dosave($data, $where);
            } else { //添加数据
                $data['c_time'] = date('Y-m-d H:i:s');
                //这里还需要设置他的简介

                $x = $this->mod->Doadd($data);
            }
            $x and $this->success('操作成功', CONTROLLER_NAME . '/index', NULL, 1) or $this->error('操作失败');
        } else {
            return $this->adminTpl();
        }
    }

    public function delete(){
        //删除文章
        $id = input('id','');
        $where['id'] = '-1';
        $ret = $this->mod->Dodel($id,$where);

        //返回文章列表页
        $ret and $this->success('操作成功', CONTROLLER_NAME . '/index', NULL, 1) or $this->error('操作失败');

    }

//编辑器图片上传 【单张上传操作，多图上传自行研究- -】
    public function UploadPic() {
        $file = request()->file('info_upload_img');
        if ($file) {
            $file_path = \think\facade\Env::get('ROOT_PATH') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';
            $img_path = DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
            $img_info = $file->move($file_path);
            if ($img_info) {
                $img = $img_path . $img_info->getSaveName();
                $ret = ["errno" => 0, 'data' => [$img]];
                return json($ret);
            } else {
                $this->error($file->getError());
            }
        }
    }

}
