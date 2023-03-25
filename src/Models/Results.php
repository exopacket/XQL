<?php

namespace App\XQL\Models;

class Results extends Model
{

    protected function schema(\XQLModel $results)
    {
        //create schema, relationships, and so forth

        $event = $results->root("event");
        $event->bind("host_events", "id", "id");
        $event->field("testing", "event_name");
        $event->attach(Session::class);

    }

}