<?php

namespace App\XQL\Examples;

use XQL\Core\XQLModel;

class ResultsTest extends XQLModel
{

    protected function schema(XQLModel $model)
    {

        dd($this->plural("person"));
        //$model->bindAll("results", "info")->enforced();
        //$model->attach(Session::class)->enforced();
        //$model->attach(Entry::class)->multiple("entries");
        $model->field("TestOne")->enforced();
        $model->field("test_two")->enforced()->multiple();
        $model->field("testThree")->searchable();
        $model->bindAll("persons")->where("id")->orWhere("name")->multiple()->searchable();

    }

    public static function test()
    {
        $id = "e0c7b34733893f0d89141c9a47924bc724131f67";
        $res = ResultsTest::fetch($id);
        dd($res->export());
//        $content = Cloud::get("results/$id.xml");
//        dd(get_object_vars(simplexml_load_string($content)->children()));
//        $res = Results::create([
//            'testOne' => 'Hello',
//            'TestTwo' => ['World', 'And', 'Universe'],
//            'test_three' => "Exclamation Point",
//            'persons' => [
//                'id' => 1,
//                "name" => "Person 2"
//            ]
//        ]);
//        dd($res->export());
    }

}