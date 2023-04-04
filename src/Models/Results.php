<?php

namespace App\XQL\Models;

use App\XQL\Classes\XQLModel;
use App\XQL\Classes\XQLObject;

class Results extends XQLModel
{

    protected function schema(XQLModel $model)
    {

        $model->field("test");

        $obj = new XQLObject("generic");
        $obj->field("test1")->value("test1");
        $obj->field("test2")->value("test2");
        $obj->field("test3")->value("test3");
        $model->plant($obj);

        $obj = new XQLObject("generic");
        $obj->field("test1")->value("test1");
        $obj->field("test2")->value("test2");
        $obj->field("test3")->value("test3");
        $model->plant($obj);



//        $model->bindAll("results", "info");
//        $model->attach(Session::class);
//        $model->attach(Entry::class)->multiple("entries");

    }

    public static function test()
    {
        $res = (new Results)->create([
            "test" => "Hello World"
        ]);
        dd($res->export());
    }

}