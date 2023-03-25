<?php

namespace App\XQL\Models;

use App\XQL\Classes\XQLModel;

class Results extends XQLModel
{

    protected function schema(XQLModel $model)
    {
        //create schema, relationships, and so forth

        $event = $model->root("event");
        $event->field("EventName")->value("Test Event");
        $event->field("event_description")->value("Test Event Description");

        $model->attach(Session::class);

    }

    public static function test() {
        (new Results())->export();
    }

}