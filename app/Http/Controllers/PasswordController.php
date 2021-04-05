<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Hash;
use Illuminate\Support\Str;
use DB;
use Mail;
use Carbon\Carbon;

class PasswordController extends Controller {

  public function __construct () {
    $this->middleware('throttle:3,10', [
      'only' => ['sendResetLinkEmail']
    ]);
  }

  public function showLinkRequestForm () {
    return view('auth.passwords.email');
  }

  public function sendResetLinkEmail (Request $request) {

    // 验证邮箱
    $request->validate(['email' => 'required|email']);
    $email = $request->email;

    // 获取对应用户
    $user = User::where('email', $email)->first();

    // 如果不存在
    if (is_null($user)) {
      session()->flash('danger', '邮箱未注册');
      return redirect()->back()->withInput();
    }

    // 生成token视图
    $token = hash_hmac('sha256', Str::random(40), config('app.key'));

    // token入库
    DB::table('password_resets')->updateOrInsert(['email' => $email], [
      'email' => $email,
      'token' => Hash::make($token),
      'created_at' => new Carbon,
    ]);

    // 发送邮件
    Mail::send('emails.reset_link', compact('token'), function ($message) use ($email) {
      $message->to($email)->subject('忘记密码');
    });
    
    session()->flash('success', '重置邮件发送成功，请查收');
    return redirect()->back();
  }

  public function showResetForm (Request $request) {
    $token = $request->route()->parameter('token');
    return view('auth.passwords.reset', compact('token'));
  }

  public function reset (Request $request) {

    // 数据是否合规
    $request->validate([
      'token' => 'required',
      'email' => 'required|email',
      'password' => 'required|confirmed|min:8'
    ]);
    $email = $request->email;
    $token = $request->token;

    // 链接有效时间
    $expires = 60 * 10;

    // 获取用户
    $user = User::where('email', $email)->first();

    // 如果不存在
    if (is_null($user)) {
      session()->flash('danger', '邮箱未注册');
      return redirect()->back()->withInput();
    }

    // 读取重置记录
    $record = (array)DB::table('password_resets')->where('email', $email)->first();

    // 记录存在
    if ($record) {
      // 是否过期
      if (Carbon::parse($record['created_at'])->addSeconds($expires)->isPast()) {
        session()->flash('danger', '令牌过期');
        return redirect()->back();
      }
    }


    // 是否存在
    if (!Hash::check($token, $record['token'])) {
      session()->flash('danger', '令牌错误');
      return redirect()->back();
    }

    // 一切正常，修改密码
    $user->update(['password' => bcrypt($request->password)]);

    // 提示用户修改成功
    session()->flash('success', '密码重置成功，请使用新密码登录');
    return redirect()->route('login');

  }

}