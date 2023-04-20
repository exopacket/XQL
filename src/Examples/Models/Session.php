<?php

namespace App\XQL\Examples\Models;

use App\XQL\Core\XQLModel;

class Session extends XQLModel
{

    protected function schema(XQLModel $model)
    {
        //create schema, relationships, and so forth

        $model->bindAll("sessions", "info")->enforced();

    }

}