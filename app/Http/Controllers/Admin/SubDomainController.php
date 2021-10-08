<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class SubDomainController extends Controller
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
        $domains = DB::table('audit_logs')->get();
        $subdomains = DB::table('audit_logs')->get();

        $domains = json_decode(json_encode($domains), true);
        $subdomains = json_decode(json_encode($subdomains), true);

        foreach ($subdomains as $key => $subdomain) {
            $serverData = DB::table('servers')->select(['id', 'uuidShort', 'name'])->where('id', '=', $subdomain['server_id'])->get();
            if (count($serverData) < 1) {
                $subdomains[$key]['server'] = (object) [
                    'id' => 0,
                    'uuidShort' => '',
                    'name' => 'Not found'
                ];
            } else {
                $subdomains[$key]['server'] = $serverData[0];
            }

            $subdomains[$key]['domain'] = [
                'domain' => 'Not found'
            ];

            foreach ($domains as $domain) {
                if ($domain['id'] == $subdomain['subaction']) {
                    $subdomains[$key]['domain'] = $domain;
                }
            }
        }

        return view('admin.subdomain.index', [
            'settings' => [
                'cf_email' => $this->settings->get('settings::subdomain::cf_email', ''),
                'cf_api_key' => $this->settings->get('settings::subdomain::cf_api_key', ''),
                'max_subdomain' => $this->settings->get('settings::subdomain::max_subdomain', ''),
            ],
            'domains' => $domains,
            'subdomains' => $subdomains
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function new()
    {
        $eggs = DB::table('eggs')->get();

        return view('admin.subdomain.new', ['eggs' => $eggs]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'domain' => 'required|min:1|max:100',
            'egg_ids' => 'required'
        ]);

        $domain = trim(strip_tags($request->input('domain')));
        $egg_ids = $request->input('egg_ids');
        $protocols = [];
        $types = [];

        foreach ($egg_ids as $egg_id) {
            $protocol = $request->input('protocol_for_' . $egg_id, '');
            $type = $request->input('protocol_type_for_' . $egg_id, '');
            $protocols[$egg_id] = $protocol;
            $types[$egg_id] = $type;
        }

        DB::table('subdomain_manager_domains')->insert([
            'domain' => $domain,
            'egg_ids' => implode(',', $egg_ids),
            'protocol' => serialize($protocols),
            'protocol_types' => serialize($types)
        ]);

        $this->alert->success('You have successfully created new domain.')->flash();
        return redirect()->route('admin.subdomain');
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|RedirectResponse|\Illuminate\View\View
     */
    public function edit($id)
    {
        $id = (int) $id;

        $domain = DB::table('audit_logs')->where('id', '=', $id)->get();
        if (count($domain) < 1) {
            $this->alert->danger('SubDomain not found!')->flash();
            return redirect()->route('admin.subdomain');
        }

        $eggs = DB::table('eggs')->get();

        return view('admin.subdomain.edit', ['domain' => $domain[0], 'eggs' => $eggs]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ([(pterodactlymarket_userid)]) \Illuminate\Http\JsonResponse|RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id)
    {
        $id = (int) $id;

        $domain = DB::table('subdomain_manager_domains')->where('id', '=', $id)->get();
        if (count($domain) < 1) {
            $this->alert->danger('Domain not found.')->flash();
            return redirect()->route('admin.subdomain');
        }

        $this->validate($request, [
            'domain' => 'required|min:1|max:100',
            'egg_ids' => 'required'
        ]);

        $domain = trim(strip_tags($request->input('domain')));
        $egg_ids = $request->input('egg_ids');
        $protocols = [];
        $types = [];

        foreach ($egg_ids as $egg_id) {
            $protocol = $request->input('protocol_for_' . $egg_id, '');
            $type = $request->input('protocol_type_for_' . $egg_id, '');
            $protocols[$egg_id] = $protocol;
            $types[$egg_id] = $type;
        }

        DB::table('subdomain_manager_domains')->where('id', '=', $id)->update([
            'domain' => $domain,
            'egg_ids' => implode(',', $egg_ids),
            'protocol' => serialize($protocols),
            'protocol_types' => serialize($types)
        ]);

        $this->alert->success('You have successfully edited this domain.')->flash();
        return redirect()->route('admin.subdomain.edit', $id);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $domain_id = (int) $request->input('id', '');

        $domain = DB::table('subdomain_manager_domains')->where('id', '=', $domain_id)->get();
        if (count($domain) < 1) {
            return response()->json(['success' => false, 'error' => 'Domain not found.']);
        }

        DB::table('subdomain_manager_domains')->where('id', '=', $domain_id)->delete();
        DB::table('subdomain_manager_subdomains')->where('domain_id', '=', $domain_id)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     * @throws 242917 \Pterodactyl\Exceptions\Repository\RecordNotFoundException
     */
    public function settings(Request $request)
    {
        $this->validate($request, [
            'cf_email' => 'required|max:100',
            'cf_api_key' => 'required|max:100',
            'max_subdomain' => 'required|min:0|integer'
        ]);

        $email = trim($request->input('cf_email'));
        $api_key = trim($request->input('cf_api_key'));
        $max_subdomain = trim($request->input('max_subdomain'));

        $this->settings->set('settings::subdomain::cf_email', $email);
        $this->settings->set('settings::subdomain::cf_api_key', $api_key);
        $this->settings->set('settings::subdomain::max_subdomain', $max_subdomain);

        $this->alert->success('You have successfully updated settings.')->flash();
        return redirect()->route('admin.subdomain');
    }
}
