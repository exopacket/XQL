<?php

namespace App\XQL\Models;

use App\XQL\Classes\XQLModel;

class Entry extends XQLModel
{

    protected function schema(XQLModel $model)
    {
        //create schema, relationships, and so forth

        $model->bindAll("sessions", "info");

    }

}