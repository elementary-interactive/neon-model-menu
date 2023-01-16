<?php

namespace Neon\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neon\Models\Traits\Uuid;
use Neon\Site\Models\Traits\SiteDependencies;

class Link extends EloquentModel
{
  use SoftDeletes;
  use Uuid;
  
}