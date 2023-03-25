<?php

namespace App\XQL\Models;

use App\XQL\Classes\XQLModel;

class Session extends XQLModel
{

    protected function schema(XQLModel $model)
    {
        //create schema, relationships, and so forth

        $event = $model->root("session");
        $event->field("EventName")->value("Test Event");
        $event->field("event_description")->value("Test Event Description");

    }

}