<?php

namespace XQL\Examples\Models;

use XQL\Core\XQLModel;

class Session extends XQLModel
{

    protected function schema(XQLModel $model)
    {
        //create schema, relationships, and so forth

        $model->bindAll("sessions", "info")->enforced();

    }

}