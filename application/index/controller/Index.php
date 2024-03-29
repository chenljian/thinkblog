<?php

namespace app\index\controller;

use app\Common\Controller\BlogBaseController;

class Index extends BlogBaseController {

    public function __construct() {
        //在父类的构造函数中有获取背景图片和文章分类信息
        parent::__construct();
    }

    public function index() {
        //参数输入
        $page = input('get.page/d', 1);
        $type = input('get.type/d', 0);
        $pageSize = 5; //每页显示5条数据 可自行修改

        $mod = new \app\admin\model\articleModel();
        $where[] = ['status', '=', 1];
        if ($type) {
            $where[] = ['type', '=', $type];
            $map[] = ['type', '=', $type];
        }

        //获取 $pageSize 条数据
        $list = $mod->getList($where, $page, $pageSize);
        if (empty($list)) {
            return $this->jump404();
        }
        //查看一共有多少条数据
        $count = $mod->getCount($where);
        $pageparam = $mod->_pageparam();
        $Page = new \think\paginator\driver\Bootstrap($list, $pageSize, $page, $count, FALSE, $pageparam);
        $show = $Page->render();

        //      顶部轮播图 start
        $map[] = ['status', '=', 1];
        $map[] = ['img', '<>', ''];
        $tops = $mod->getField($map, 'id,title,img', 'id desc', 3);  //获取存在文章缩略图片的前5条数据
        //      顶部轮播图 end
        
        $this->assign('page', $show);
        $this->assign('list', $list);
        $this->assign('type', $type);
        $this->assign('tops', $tops);
        return $this->blogTpl();
        
    }

}
