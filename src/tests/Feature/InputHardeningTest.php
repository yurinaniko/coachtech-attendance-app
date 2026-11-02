<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\User;

/**
 * URL・クエリの日付/月を検証せずに Carbon に渡していたため、
 * 不正な値で 500（内部エラー）になったり、意図しない勤怠行が作られたりしていた。
 * 不正な値は「存在しないページ」として 404 で返すことを固定する。
 */
class InputHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }

    /**
     * @test
     * @dataProvider invalidDates
     */
    public function attendance_detail_with_invalid_date_returns_404_without_creating_rows(string $date)
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/attendance/detail/' . $date)
            ->assertNotFound();

        $this->assertSame(0, Attendance::where('user_id', $user->id)->count());
    }

    public static function invalidDates(): array
    {
        return [
            '文字列'       => ['abc'],
            '相対表現'     => ['yesterday'],
            '存在しない日' => ['2026-02-30'],
            '形式違い'     => ['2026-1-5'],
        ];
    }

    /** @test */
    public function attendance_detail_with_valid_date_still_works()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/attendance/detail/' . now()->subDay()->format('Y-m-d'))
            ->assertOk();
    }

    /** @test */
    public function attendance_list_with_invalid_month_returns_404()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/attendance/list?month=abc')->assertNotFound();
        $this->actingAs($user)->get('/attendance/list?month=2026-13')->assertNotFound();
    }

    /** @test */
    public function admin_pages_with_invalid_date_or_month_return_404()
    {
        $admin = $this->admin();
        $staff = User::factory()->create();

        $this->actingAs($admin)->get('/admin/attendance/list?date=abc')->assertNotFound();
        $this->actingAs($admin)->get("/admin/staff/{$staff->id}/attendances?month=abc")->assertNotFound();
        $this->actingAs($admin)->get("/admin/staff/{$staff->id}/attendances/csv?month=abc")->assertNotFound();

        $this->assertSame(0, Attendance::where('user_id', $staff->id)->count());
    }

    /** @test */
    public function csv_filename_with_quote_in_user_name_is_safely_encoded()
    {
        $admin = $this->admin();
        $staff = User::factory()->create(['name' => 'a"b']);

        $response = $this->actingAs($admin)
            ->get("/admin/staff/{$staff->id}/attendances/csv?month=2026-10");

        $response->assertOk();
        $disposition = $response->headers->get('Content-Disposition');

        // 引用符で区切りが壊れず、日本語名にも対応する RFC 6266 形式（filename*）で出す
        $this->assertStringStartsWith('attachment;', $disposition);
        $this->assertStringContainsString("filename*=utf-8''a%22b_2026-10.csv", $disposition);
        $this->assertStringNotContainsString('a"b', $disposition);
    }
}
