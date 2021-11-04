<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class AuditLogsController extends Controller
{
    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    protected $alert;

    /**
     * @var \Pterodactyl\Contracts\Repository\SettingsRepositoryInterface
     */
    private $settings;

    /**
     * SubDomainController constructor.
     * @param AlertsMessageBag $alert
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(AlertsMessageBag $alert, SettingsRepositoryInterface $settings)
    {
        $this->alert = $alert;
        $this->settings = $settings;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $logs = DB::table('audit_logs')->get();
        $logs = json_decode(json_encode($logs), true);
        return view('admin.logs.index', [
            'logs' => $logs,
        ]);
    }
}
