<?php

namespace App\XQL\Models;

use App\XQL\Classes\XQLModel;

class Results extends XQLModel
{

    protected function schema(XQLModel $model)
    {

        $model->field("test");
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