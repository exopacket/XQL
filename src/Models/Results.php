<?php

namespace App\XQL\Models;

use App\XQL\Classes\XQLModel;
use App\XQL\Classes\XQLObject;

class Results extends XQLModel
{

    protected function schema(XQLModel $model)
    {

        //$model->bindAll("results", "info")->enforced();
        //$model->attach(Session::class)->enforced();
        //$model->attach(Entry::class)->multiple("entries");
        $model->field("test1")->enforced();
        $model->field("test2")->enforced()->multiple();
        $model->field("test3");

    }

    public static function test()
    {
        $res = Results::create([
            'test1' => 'Hello',
            'test2' => ['World', 'And', 'Universe']
        ]);
        dd($res->export());
    }

}