<?php 

namespace App\Http\Controllers;

use App\Login;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginsController extends Controller {
    
	public function all() {
		return response()->json(Login::all());
	}
}
?>
