<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    //No.3 ログイン認証機能（一般ユーザー）
    public function test_email_is_required_for_login()
    {
        $response = $this->post('/login',[
            'email'=>'',
            'password'=>'password123',
        ]);

        $response->assertSessionHasErrors([
            'email'=>'メールアドレスを入力してください'
        ]);
    }

    public function test_password_is_required_for_login()
    {
        $response=$this->post('/login',[
            'email'=>'test@example.com',
            'password'=>'',
        ]);

        $response->assertSessionHasErrors([
            'password'=>'パスワードを入力してください'
        ]);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $response = $this->post('/login',[
            'email'=>'miss@example.com',
            'password'=>'password123'
        ]);

        $response->assertSessionHasErrors([
            'email'=>'ログイン情報が登録されていません']);
    }

    // No.3 管理者ログイン認証機能
    public function test_email_is_required_for_admin_login()
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);
    }

    public function test_password_is_required_for_admin_login()
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください'
        ]);
    }

    public function test_admin_login_fails_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'miss@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);
    }

    //No.4 日時取得機能
    public function test_current_date_is_displayed()
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee(
            now()->format('Y年n月j日')
        );
    }

    // No.5 ステータス確認機能
    public function test_status_is_off_duty()
    {
        $user = User::factory()->create([
            'role'=>'user'
        ]);

        $response=$this->actingAs($user)
            ->get('/attendance');

        $response->assertSee('勤務外');
    }

    public function test_status_is_working()
    {
        $user = User::factory()->create([
            'role'=>'user'
        ]);

        \App\Models\Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in_at'=>now(),
            'status'=>'出勤中',
        ]);

        $response=$this->actingAs($user)
            ->get('/attendance');

        $response->assertSee('出勤中');
    }

    public function test_status_is_on_break()
    {
        $user = User::factory()->create([
            'role'=>'user'
        ]);

        \App\Models\Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in_at'=>now(),
            'status'=>'休憩中',
        ]);

        $response=$this->actingAs($user)
            ->get('/attendance');

        $response->assertSee('休憩中');
    }

    public function test_status_is_finished()
    {
        $user = User::factory()->create([
            'role'=>'user'
        ]);

        \App\Models\Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in_at'=>now(),
            'clock_out_at'=>now(),
            'status'=>'退勤済',
        ]);

        $response=$this->actingAs($user)
            ->get('/attendance');

        $response->assertSee('退勤済');
    }

    //No.6 出勤機能
    public function test_user_can_clock_in()
    {
        $user = User::factory()->create([
            'role'=>'user'
        ]);

        $this->actingAs($user)
            ->post('/attendance/clock-in');

        $this->assertDatabaseHas('attendances', [
            'user_id'=>$user->id,
            'work_date'=>today()->toDateString(),
            'status'=>'出勤中',
        ]);
    }

    //No.7 休憩機能
    public function test_user_can_start_break()
    {
        $user = User::factory()->create([
            'role' => 'user'
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
            'status' => '出勤中',
        ]);

        $this->actingAs($user)
            ->post('/attendance/break-start');

        $this->assertDatabaseHas('breaks',[
            'attendance_id' => $attendance->id,
        ]);

        $this->assertDatabaseHas('attendances',[
            'user_id'=>$user->id,
            'status'=>'休憩中'
        ]);
    }

    public function test_user_can_end_break()
    {
        $user = User::factory()->create([
            'role' => 'user'
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
            'status' => '休憩中',
        ]);

        \App\Models\BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start_at' => now(),
        ]);

        $this->actingAs($user)
            ->post('/attendance/break-end');

        $this->assertDatabaseHas('attendances',[
            'user_id'=>$user->id,
            'status'=>'出勤中'
        ]);
    }

    // No.8 退勤機能
    public function test_user_can_clock_out()
    {
        $user = User::factory()->create([
            'role' => 'user'
        ]);

        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
            'status' => '出勤中',
        ]);

        $this->actingAs($user)
            ->post('/attendance/clock-out');

        $this->assertDatabaseHas('attendances',[
            'user_id'=>$user->id,
            'status'=>'退勤済'
        ]);
    }

    //勤怠一覧表示
    public function test_user_can_see_own_attendance_list()
    {
        $user = User::factory()->create([
            'role' => 'user'
        ]);

        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);

        $response->assertSee(today()->toDateString());
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_user_can_view_previous_month_attendance_list()
    {
        $user = User::factory()->create([
            'role' => 'user'
        ]);

        $previousMonth = now()->subMonth()->format('Y-m');

        $response = $this->actingAs($user)
            ->get('/attendance/list?month=' . $previousMonth);

        $response->assertStatus(200);
        $response->assertSee(now()->subMonth()->format('Y-m'));
    }

    public function test_user_can_view_next_month_attendance_list()
    {
        $user = User::factory()->create([
            'role' => 'user'
        ]);

        $nextMonth = now()->addMonth()->format('Y-m');

        $response = $this->actingAs($user)
            ->get('/attendance/list?month=' . $nextMonth);

        $response->assertStatus(200);

        $response->assertSee(
            now()->addMonth()->format('Y-m')
        );
    }

    //勤怠詳細画面表示
    public function test_user_can_view_attendance_detail()
    {
        $user = User::factory()->create([
            'role' => 'user'
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    //勤怠修正申請機能（申請保存）
    public function test_user_can_request_attendance_correction()
    {
        $user = User::factory()->create([
            'role' => 'user'
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($user)
            ->post('/attendance/detail/'.$attendance->id.'/request',[
                'requested_clock_in_at'=>'10:00',
                'requested_clock_out_at'=>'19:00',
                'reason'=>'電車遅延',
                'break_start_at'=>['12:00'],
                'break_end_at'=>['13:00'],
            ]);

        $this->assertDatabaseHas(
            'stamp_correction_requests',
            [
                'attendance_id'=>$attendance->id,
                'reason'=>'電車遅延',
                'status'=>'承認待ち',
            ]
        );
    }

    //申請一覧表示（承認待ち）
    public function test_user_can_view_request_list()
    {
        $user = User::factory()->create([
            'role'=>'user'
        ]);

        $attendance=\App\Models\Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in_at'=>'09:00:00',
            'clock_out_at'=>'18:00:00',
            'status'=>'退勤済',
        ]);

        \App\Models\StampCorrectionRequest::create([
            'attendance_id'=>$attendance->id,
            'user_id'=>$user->id,
            'requested_clock_in_at'=>'10:00',
            'requested_clock_out_at'=>'19:00',
            'reason'=>'電車遅延',
            'status'=>'承認待ち',
            'requested_at'=>now(),
        ]);

        $response=$this->actingAs($user)
            ->get('/stamp_correction_request/list');

        $response->assertStatus(200);

        $response->assertSee('承認待ち');
    }

    public function test_user_can_view_approved_request_list()
    {
        $user = User::factory()->create([
            'role'=>'user'
        ]);

        $attendance=\App\Models\Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in_at'=>'09:00:00',
            'clock_out_at'=>'18:00:00',
            'status'=>'退勤済',
        ]);

        \App\Models\StampCorrectionRequest::create([
            'attendance_id'=>$attendance->id,
            'user_id'=>$user->id,
            'requested_clock_in_at'=>'10:00',
            'requested_clock_out_at'=>'19:00',
            'reason'=>'電車遅延',
            'status'=>'承認済み',
            'requested_at'=>now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/stamp_correction_request/list?status=承認済み');

        $response->assertStatus(200);

        $response->assertSee('承認済み');
    }

    //管理者の勤怠一覧
    public function test_admin_can_view_attendance_list()
    {
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);

        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'role' => 'user'
        ]);

        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list');

        $response->assertStatus(200);

        $response->assertSee('テスト太郎');
        $response->assertSee('09:00');
    }




    // 管理者側のスタッフ一覧
    public function test_admin_can_view_staff_list()
    {
        $admin = User::factory()->create([
            'role'=>'admin'
        ]);

        User::factory()->create([
            'name'=>'テスト太郎',
            'role'=>'user'
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/staff/list');

        $response->assertStatus(200);

        $response->assertSee('テスト太郎');
    }

    //管理者：スタッフ月次勤怠一覧
    public function test_admin_can_view_staff_monthly_attendance()
    {
        $admin = User::factory()->create([
            'role'=>'admin'
        ]);

        $user = User::factory()->create([
            'name'=>'テスト太郎',
            'role'=>'user'
        ]);

        \App\Models\Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in_at'=>'09:00:00',
            'clock_out_at'=>'18:00:00',
            'status'=>'退勤済',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/staff/' . $user->id);

        $response->assertStatus(200);

        $response->assertSee('テスト太郎');
        $response->assertSee('09:00');
    }

    //管理者：申請一覧表示
    public function test_admin_can_view_request_list()
    {
        $admin = User::factory()->create([
            'role'=>'admin'
        ]);

        $user = User::factory()->create([
            'role'=>'user'
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in_at'=>'09:00:00',
            'clock_out_at'=>'18:00:00',
            'status'=>'退勤済',
        ]);

        \App\Models\StampCorrectionRequest::create([
            'attendance_id'=>$attendance->id,
            'user_id'=>$user->id,
            'requested_clock_in_at'=>'10:00',
            'requested_clock_out_at'=>'19:00',
            'reason'=>'電車遅延',
            'status'=>'承認待ち',
            'requested_at'=>now(),
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/stamp_correction_request/list');

        $response->assertStatus(200);

        $response->assertSee('承認待ち');
    }

    //管理者：申請詳細表示
    public function test_admin_can_view_request_detail()
    {
        $admin = User::factory()->create([
            'role'=>'admin'
        ]);

        $user = User::factory()->create([
            'name'=>'テスト太郎',
            'role'=>'user'
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in_at'=>'09:00:00',
            'clock_out_at'=>'18:00:00',
            'status'=>'退勤済',
        ]);

        $request = \App\Models\StampCorrectionRequest::create([
            'attendance_id'=>$attendance->id,
            'user_id'=>$user->id,
            'requested_clock_in_at'=>'10:00',
            'requested_clock_out_at'=>'19:00',
            'reason'=>'電車遅延',
            'status'=>'承認待ち',
            'requested_at'=>now(),
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/stamp_correction_request/' . $request->id);

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
        $response->assertSee('電車遅延');
    }

    //管理者：承認機能
    public function test_admin_can_approve_request()
    {
        $admin = User::factory()->create([
            'role'=>'admin'
        ]);

        $user = User::factory()->create([
            'role'=>'user'
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in_at'=>'09:00:00',
            'clock_out_at'=>'18:00:00',
            'status'=>'退勤済',
        ]);

        $request =       \App\Models\StampCorrectionRequest::create([
            'attendance_id'=>$attendance->id,
            'user_id'=>$user->id,
            'requested_clock_in_at'=>'10:00',
            'requested_clock_out_at'=>'19:00',
            'reason'=>'電車遅延',
            'status'=>'承認待ち',
            'requested_at'=>now(),
        ]);

        $this->actingAs($admin)
            ->post('/admin/stamp_correction_request/' . $request->id . '/approve');

        $this->assertDatabaseHas('stamp_correction_requests', [
            'id'=>$request->id,
            'status'=>'承認済み',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id'=>$attendance->id,
            'clock_in_at'=>'10:00:00',
            'clock_out_at'=>'19:00:00',
        ]);
    }

    //管理者：承認済み一覧表示
    public function test_admin_can_view_approved_request_list()
    {
        $admin = User::factory()->create([
            'role'=>'admin'
        ]);

        $user = User::factory()->create([
            'role'=>'user'
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in_at'=>'09:00:00',
            'clock_out_at'=>'18:00:00',
            'status'=>'退勤済',
        ]);

        \App\Models\StampCorrectionRequest::create([
            'attendance_id'=>$attendance->id,
            'user_id'=>$user->id,
            'requested_clock_in_at'=>'10:00',
            'requested_clock_out_at'=>'19:00',
            'reason'=>'電車遅延',
            'status'=>'承認済み',
            'requested_at'=>now(),
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/stamp_correction_request/list?status=承認済み');

        $response->assertStatus(200);

        $response->assertSee('承認済み');
    }

    //管理者：勤怠詳細表示
    public function test_admin_can_view_attendance_detail()
    {
        $admin = User::factory()->create([
            'role'=>'admin'
        ]);

        $user = User::factory()->create([
            'name'=>'テスト太郎',
            'role'=>'user'
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in_at'=>'09:00:00',
            'clock_out_at'=>'18:00:00',
            'status'=>'退勤済',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/' . $attendance->id);

        $response->assertStatus(200);

        $response->assertSee('テスト太郎');
        $response->assertSee('09:00');
    }

    // 管理者：勤怠修正機能
    public function test_admin_can_update_attendance()
    {
        $admin = User::factory()->create([
            'role'=>'admin'
        ]);

        $user = User::factory()->create([
            'role'=>'user'
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in_at'=>'09:00:00',
            'clock_out_at'=>'18:00:00',
            'status'=>'退勤済',
        ]);

        $this->actingAs($admin)
            ->post('/admin/attendance/'.$attendance->id.'/update',[
                'requested_clock_in_at'=>'10:00',
                'requested_clock_out_at'=>'19:00',
                'reason'=>'管理者修正',
                'break_start_at'=>['12:00'],
                'break_end_at'=>['13:00'],
            ]);

        $this->assertDatabaseHas('attendances',[
            'id'=>$attendance->id,
            'note'=>'管理者修正',
        ]);
    }

    //管理者：CSV出力機能
    public function test_admin_can_export_staff_csv()
    {
        $admin = User::factory()->create([
            'role'=>'admin'
        ]);

        $user = User::factory()->create([
            'role'=>'user'
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/staff/'.$user->id.'/csv');

        $response->assertStatus(200);

        $response->assertHeader(
            'content-type',
            'text/csv; charset=UTF-8'
        );
    }

    // 管理者権限チェック
    public function test_user_cannot_access_admin_page()
    {
        $user = User::factory()->create([
            'role'=>'user'
        ]);

        $response = $this->actingAs($user)
            ->get('/admin/attendance/list');

        $response->assertStatus(403);
    }

    // 未ログインアクセス制御
    public function test_guest_is_redirected_to_login()
    {
        $response = $this->get('/attendance');

        $response->assertRedirect('/login');
    }

    // 未ログインで管理者ページアクセス制御
    public function test_guest_cannot_access_admin_page()
    {
        $response = $this->get('/admin/attendance/list');

        $response->assertRedirect('/login');
    }

    // 未ログインは勤怠一覧にアクセスできない
    public function test_guest_cannot_access_attendance_page()
    {
        $response = $this->get('/attendance/list');

        $response->assertRedirect('/login');
    }

    // 一般ユーザーは他人の勤怠詳細を見れない
    public function test_user_cannot_view_other_user_attendance_detail()
    {
        $user = User::factory()->create([
            'role'=>'user'
        ]);

        $otherUser = User::factory()->create([
            'role'=>'user'
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id'=>$otherUser->id,
            'work_date'=>today(),
            'clock_in_at'=>'09:00:00',
            'clock_out_at'=>'18:00:00',
            'status'=>'退勤済',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/detail/'.$attendance->id);

        $response->assertStatus(404);
    }

    // 承認待ち中の勤怠修正制御
    public function test_user_cannot_edit_attendance_when_request_is_pending()
    {
        $user = User::factory()->create([
            'role'=>'user'
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in_at'=>'09:00:00',
            'clock_out_at'=>'18:00:00',
            'status'=>'退勤済',
        ]);

        \App\Models\StampCorrectionRequest::create([
            'attendance_id'=>$attendance->id,
            'user_id'=>$user->id,
            'requested_clock_in_at'=>'10:00',
            'requested_clock_out_at'=>'19:00',
            'reason'=>'電車遅延',
            'status'=>'承認待ち',
            'requested_at'=>now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/detail/'.$attendance->id);

        $response->assertStatus(200);
        $response->assertSee('承認待ちのため修正はできません');
        $response->assertDontSee('修正</button>', false);
    }

    // 管理者は一般ユーザー勤怠詳細を閲覧できる
    public function test_admin_can_view_other_user_attendance_detail()
    {
        $admin = User::factory()->create([
            'role'=>'admin'
        ]);

        $user = User::factory()->create([
            'name'=>'テスト太郎',
            'role'=>'user'
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in_at'=>'09:00:00',
            'clock_out_at'=>'18:00:00',
            'status'=>'退勤済',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/'.$attendance->id);

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
    }

    // 管理者：スタッフ月移動
    public function test_admin_can_view_next_month_staff_attendance()
    {
        $admin = User::factory()->create([
            'role'=>'admin'
        ]);

        $user = User::factory()->create([
            'role'=>'user'
        ]);

        $nextMonth = now()->addMonth()->format('Y-m');

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/staff/'.$user->id.'?month='.$nextMonth);

        $response->assertStatus(200);

        $response->assertSee(
            now()->addMonth()->format('Y-m')
        );
    }

    // 管理者：スタッフ前月移動
    public function test_admin_can_view_previous_month_staff_attendance()
    {
        $admin = User::factory()->create([
            'role'=>'admin'
        ]);

        $user = User::factory()->create([
            'role'=>'user'
        ]);

        $previousMonth = now()->subMonth()->format('Y-m');

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/staff/'.$user->id.'?month='.$previousMonth);

        $response->assertStatus(200);

        $response->assertSee(
            now()->subMonth()->format('Y-m')
        );
    }

    // 管理者：日次勤怠一覧の前日移動
    public function test_admin_can_view_previous_day_attendance_list()
    {
        $admin = User::factory()->create([
            'role'=>'admin'
        ]);

        $previousDate = now()->subDay()->toDateString();

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list?date='.$previousDate);

        $response->assertStatus(200);

        $response->assertSee(
            now()->subDay()->format('Y/m/d')
        );
    }

    // 管理者：日次勤怠一覧の翌日移動
    public function test_admin_can_view_next_day_attendance_list()
    {
        $admin = User::factory()->create([
            'role'=>'admin'
        ]);

        $nextDate = now()->addDay()->toDateString();

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list?date='.$nextDate);

        $response->assertStatus(200);

        $response->assertSee(
            now()->addDay()->format('Y/m/d')
        );
    }

    // 管理者：承認時に処理日時が保存される
    public function test_processed_at_is_saved_when_request_is_approved()
    {
        $admin = User::factory()->create([
            'role'=>'admin'
        ]);

        $user = User::factory()->create([
            'role'=>'user'
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in_at'=>'09:00:00',
            'clock_out_at'=>'18:00:00',
            'status'=>'退勤済',
        ]);

        $request = \App\Models\StampCorrectionRequest::create([
            'attendance_id'=>$attendance->id,
            'user_id'=>$user->id,
            'requested_clock_in_at'=>'10:00',
            'requested_clock_out_at'=>'19:00',
            'reason'=>'電車遅延',
            'status'=>'承認待ち',
            'requested_at'=>now(),
        ]);

        $this->actingAs($admin)
            ->post('/admin/stamp_correction_request/'.$request->id.'/approve');

        $this->assertDatabaseMissing(
            'stamp_correction_requests',
            ['processed_at'=>null]
        );
    }

    // 勤怠修正申請：休憩情報保存
    public function test_break_time_is_saved_when_user_requests_attendance_correction()
    {
        $user = User::factory()->create([
            'role'=>'user'
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in_at'=>'09:00:00',
            'clock_out_at'=>'18:00:00',
            'status'=>'退勤済',
        ]);

        $this->actingAs($user)
            ->post('/attendance/detail/'.$attendance->id.'/request',[
                'requested_clock_in_at'=>'10:00',
                'requested_clock_out_at'=>'19:00',
                'reason'=>'電車遅延',
                'break_start_at'=>['12:00'],
                'break_end_at'=>['13:00'],
            ]);

        $this->assertDatabaseHas('stamp_correction_request_breaks',[
            'requested_break_start_at'=>'12:00:00',
            'requested_break_end_at'=>'13:00:00',
        ]);
    }

    //メール認証機能
    public function test_unverified_user_is_redirected_to_email_verification_notice()
{
    $user = User::factory()->create([
        'role'=>'user',
        'email_verified_at'=>null,
    ]);

    $response = $this->actingAs($user)
        ->get('/attendance');

    $response->assertRedirect('/email/verify');
}
}
