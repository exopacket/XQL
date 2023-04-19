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
        $model->field("TestOne")->enforced();
        $model->field("test_two")->enforced()->multiple();
        $model->field("testThree");
        $model->bindAll("persons")->where("id")->orWhere("name")->multiple();

    }

    public static function test()
    {
        $res = Results::create([
            'testOne' => 'Hello',
            'TestTwo' => ['World', 'And', 'Universe'],
            'test_three' => "Exclamation Point",
            'persons' => [
                'id' => 2,
                "name" => "Person 2"
            ]
        ]);
        dd($res->export());
    }

}