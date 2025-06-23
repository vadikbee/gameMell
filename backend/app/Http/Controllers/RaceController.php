namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RaceController extends Controller
{
    public function generatePaths(Request $request)
    {
        // Параметры из запроса
        $duration = $request->input('duration', 30000);
        $ticks = $request->input('ticks', 100);
        $cockroachCount = $request->input('cockroaches', 7);
        $grid = config('race.grid'); // Конфигурация сетки

        // Генератор путей (реализовать в сервисе)
        $pathGenerator = app()->make(PathGenerator::class);
        $paths = $pathGenerator->generate($grid, $cockroachCount, $ticks);

        return response()->json([
            'paths' => $paths,
            'duration' => $duration
        ]);
    }
}