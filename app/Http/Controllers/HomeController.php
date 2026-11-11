<?php

namespace App\Http\Controllers;

use App\Models\CommunityMember;
use App\Models\CommunityStory;
use App\Models\HelpRequest;
use App\Models\ServiceBooking;
use App\Models\ServiceOffer;
use App\Models\SkillCategory;
use App\Models\TimeActivity;
use App\Models\TimeLedger;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('app.dashboard');
        }

        return view('home');
    }

    public function how()
    {
        return view('public.page', ['title' => 'كيف تعمل المنصة؟', 'view' => 'how']);
    }

    public function skills()
    {
        return view('public.skills', ['categories' => SkillCategory::withCount(['offers' => fn ($q) => $q->where('status', 'published')])->orderBy('sort_order')->get()]);
    }

    public function explore(Request $request)
    {
        $offers = ServiceOffer::with(['member', 'category'])->where('status', 'published')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($q) => $q->where('title', 'like', '%'.$request->string('q').'%')->orWhere('description', 'like', '%'.$request->string('q').'%')))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->integer('category')))
            ->latest()->paginate(12)->withQueryString();

        return view('public.explore', ['offers' => $offers, 'categories' => SkillCategory::orderBy('sort_order')->get()]);
    }

    public function offer(ServiceOffer $serviceOffer)
    {
        return view('public.offer', ['offer' => $serviceOffer->load(['member', 'category'])]);
    }

    public function dashboard()
    {
        $member = Auth::user()->member;
        abort_unless($member, 422, 'يرجى استكمال ملف العضو أولًا.');
        $balance = $member->ledgerMinutes;
        $reserved = ServiceBooking::where('requester_id', Auth::id())->whereIn('status', ['pending', 'confirmed', 'in_progress', 'awaiting_confirmation'])->sum('requested_minutes');

        return view('app.dashboard', ['member' => $member, 'offers' => $member->offers()->latest()->get(), 'bookings' => ServiceBooking::with(['offer.member'])->where(fn ($q) => $q->where('requester_id', Auth::id())->orWhere('provider_id', Auth::id()))->latest()->limit(8)->get(), 'balance' => $balance, 'reserved' => $reserved]);
    }

    public function createOffer()
    {
        return view('app.offer-form', ['categories' => SkillCategory::orderBy('sort_order')->get()]);
    }

    public function operations()
    {
        abort_unless(in_array(Auth::user()->role, ['operations', 'admin'], true), 403);

        return view('staff.dashboard', ['mode' => 'operations', 'title' => 'لوحة التشغيل', 'openBookings' => ServiceBooking::whereIn('status', ['pending', 'awaiting_confirmation'])->count(), 'members' => CommunityMember::count(), 'offers' => ServiceOffer::where('status', 'published')->count(), 'bookings' => ServiceBooking::with(['offer', 'requester', 'provider'])->latest()->limit(20)->get()]);
    }

    public function admin(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $search = trim($request->string('q')->toString());
        $users = User::query()
            ->with('member')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('member', fn ($member) => $member->where('city', 'like', "%{$search}%")->orWhere('skills', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(12, ['*'], 'users_page')
            ->withQueryString();

        $ledgerBalances = TimeLedger::query()
            ->select('member_id')
            ->selectRaw('COALESCE(SUM(minutes), 0) as balance_minutes')
            ->groupBy('member_id')
            ->pluck('balance_minutes', 'member_id');

        return view('staff.dashboard', [
            'mode' => 'admin',
            'title' => 'لوحة تحكم المدير',
            'search' => $search,
            'users' => $users,
            'ledgerBalances' => $ledgerBalances,
            'offersList' => ServiceOffer::with(['member.user', 'category'])->latest()->paginate(12, ['*'], 'offers_page')->withQueryString(),
            'requestsList' => HelpRequest::with(['requester', 'helper'])->latest()->paginate(12, ['*'], 'requests_page')->withQueryString(),
            'bookings' => ServiceBooking::with(['offer', 'requester', 'provider'])->latest()->paginate(12, ['*'], 'bookings_page')->withQueryString(),
            'ledgerEntries' => TimeLedger::with(['member', 'booking.offer', 'member.user'])->latest()->paginate(12, ['*'], 'ledger_page')->withQueryString(),
            'activities' => TimeActivity::with('member.user')->latest()->paginate(12, ['*'], 'activities_page')->withQueryString(),
            'categories' => SkillCategory::withCount('offers')->orderBy('sort_order')->get(),
            'stories' => CommunityStory::latest()->get(),
            'stats' => [
                'users' => User::count(),
                'members' => CommunityMember::count(),
                'offers' => ServiceOffer::count(),
                'requests' => HelpRequest::count(),
                'open_requests' => HelpRequest::where('status', 'open')->count(),
                'bookings' => ServiceBooking::count(),
                'settled' => ServiceBooking::where('status', 'settled')->count(),
                'minutes' => (int) TimeLedger::sum('minutes'),
            ],
        ]);
    }

    public function storeOffer(Request $request)
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:180'], 'description' => ['required', 'string', 'max:2000'], 'category_id' => ['required', 'exists:skill_categories,id'], 'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'], 'delivery_mode' => ['required', 'in:remote,in_person,both'], 'city' => ['nullable', 'string', 'max:100'], 'availability' => ['nullable', 'string', 'max:255']]);
        $member = Auth::user()->member;
        abort_unless($member, 422);
        $data['member_id'] = $member->id;
        $data['status'] = 'published';
        $offer = ServiceOffer::create($data);

        return redirect()->route('offers.show', $offer)->with('success', 'تم نشر مهارتك بنجاح.');
    }

    public function book(Request $request, ServiceOffer $serviceOffer)
    {
        $data = $request->validate(['requested_minutes' => ['required', 'integer', 'min:15', 'max:480'], 'scheduled_at' => ['nullable', 'date'], 'notes' => ['nullable', 'string', 'max:1000']]);
        abort_if(! $serviceOffer->member?->user_id, 422, 'هذا العرض التعريفي غير مرتبط بحساب قابل لاستقبال الطلبات بعد.');
        abort_if($serviceOffer->member?->user_id === Auth::id(), 422, 'لا يمكنك طلب خدمتك من نفسك.');
        $member = Auth::user()->member;
        abort_unless($member, 422);
        $booking = DB::transaction(function () use ($data, $serviceOffer, $member) {
            $locked = $member->ledger()->lockForUpdate()->sum('minutes');
            $reserved = ServiceBooking::where('requester_id', Auth::id())->whereIn('status', ['pending', 'confirmed', 'in_progress', 'awaiting_confirmation'])->lockForUpdate()->sum('requested_minutes');
            abort_if(($locked - $reserved) < $data['requested_minutes'], 422, 'رصيدك المتاح لا يكفي لهذه المدة.');

            return ServiceBooking::create(['offer_id' => $serviceOffer->id, 'requester_id' => Auth::id(), 'provider_id' => $serviceOffer->member->user_id, 'requested_minutes' => $data['requested_minutes'], 'scheduled_at' => $data['scheduled_at'] ?? null, 'notes' => $data['notes'] ?? null, 'status' => 'pending']);
        });

        return redirect()->route('app.dashboard')->with('success', 'أُرسل طلب الخدمة إلى مقدمها.');
    }

    public function confirmBooking(ServiceBooking $booking)
    {
        abort_unless(in_array(Auth::id(), [$booking->requester_id, $booking->provider_id], true), 403);
        $booking->update([Auth::id() === $booking->requester_id ? 'requester_confirmed' : 'provider_confirmed' => true]);
        if ($booking->fresh()->requester_confirmed && $booking->provider_confirmed) {
            $booking->update(['status' => 'awaiting_confirmation']);
        }

        return back()->with('success', 'تم تسجيل تأكيدك.');
    }

    public function settleBooking(ServiceBooking $booking)
    {
        abort_unless(Auth::user()->role === 'admin' || Auth::user()->role === 'operations', 403);
        abort_if($booking->status === 'settled', 409, 'تمت تسوية هذه الخدمة مسبقًا.');
        $booking->load(['requester.member', 'provider.member']);
        DB::transaction(function () use ($booking) {
            $booking->update(['actual_minutes' => $booking->actual_minutes ?: $booking->requested_minutes, 'status' => 'settled', 'settled_at' => now()]);
            $minutes = $booking->actual_minutes ?: $booking->requested_minutes;
            TimeLedger::create(['member_id' => $booking->requester->member->id, 'booking_id' => $booking->id, 'minutes' => -$minutes, 'entry_type' => 'debit', 'description' => 'استخدام خدمة: '.$booking->offer->title, 'approved_by' => Auth::id()]);
            TimeLedger::create(['member_id' => $booking->provider->member->id, 'booking_id' => $booking->id, 'minutes' => $minutes, 'entry_type' => 'credit', 'description' => 'تقديم خدمة: '.$booking->offer->title, 'approved_by' => Auth::id()]);
        });

        return back()->with('success', 'تمت تسوية الساعات وتحديث الرصيد مرة واحدة.');
    }

    public function data(): JsonResponse
    {
        $member = Auth::user()?->member;
        $activities = $member ? $member->activities()->latest()->limit(10)->get() : collect();

        return response()->json([
            'members' => CommunityMember::query()->latest()->get(),
            'requests' => HelpRequest::query()->where('status', 'open')->latest()->get(),
            'activities' => $activities,
            'categories' => SkillCategory::query()->orderBy('sort_order')->get(),
            'stories' => CommunityStory::query()->where('published', true)->latest()->get(),
            'stats' => [
                'members' => CommunityMember::count(),
                'hours' => (int) TimeActivity::where('hours', '>', 0)->sum('hours'),
                'skills' => CommunityMember::query()->distinct('category')->count('category'),
                'helped' => HelpRequest::where('status', 'fulfilled')->count(),
            ],
            'auth' => Auth::check() ? ['id' => Auth::id(), 'name' => Auth::user()->name, 'member_id' => $member?->id, 'balance' => $member?->available_hours ?? 0] : null,
        ]);
    }

    public function signup(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'city' => ['required', 'string', 'max:80'],
            'skills' => ['nullable', 'string', 'max:500'],
            'needs' => ['nullable', 'string', 'max:500'],
        ], [
            'email.unique' => 'هذا البريد الإلكتروني مستخدم بالفعل. جرّب تسجيل الدخول بدلًا من إنشاء حساب جديد.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',
        ]);

        [$user, $member] = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $member = CommunityMember::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'city' => $data['city'],
                'skills' => $data['skills'] ?? null,
                'needs' => $data['needs'] ?? null,
                'initial' => mb_substr($data['name'], 0, 1),
                'rating' => 5,
            ]);

            return [$user, $member];
        });

        Auth::login($user);

        $request->session()->regenerate();

        return response()->json(['message' => 'تم إنشاء حسابك وتسجيل دخولك بنجاح.', 'member' => $member], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (! Auth::attempt($credentials, true)) {
            return response()->json(['message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.'], 422);
        }
        $request->session()->regenerate();

        return response()->json(['message' => 'تم تسجيل الدخول بنجاح.', 'user' => Auth::user()]);
    }

    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (! $request->expectsJson()) {
            return redirect()->route('home');
        }

        return response()->json(['message' => 'تم تسجيل الخروج.']);
    }

    public function donate(Request $request): JsonResponse
    {
        abort_unless(Auth::check(), 401, 'يجب تسجيل الدخول أولًا.');
        $data = $request->validate([
            'skill' => ['required', 'string', 'max:120'],
            'hours' => ['required', 'integer', 'min:1', 'max:24'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data) {
            $member = Auth::user()->member;
            if ($member) {
                $member->update([
                    'skill' => $data['skill'],
                    'category' => $data['skill'],
                    'description' => $data['description'] ?: 'أشارك ساعة من خبرتي مع المجتمع.',
                    'place' => $member->city.' · عن بُعد أو حضوريًا',
                ]);
            }
            TimeActivity::create([
                'member_id' => $member?->id,
                'title' => $data['description'] ?: $data['skill'],
                'hours' => $data['hours'],
                'activity_date' => now()->toDateString(),
            ]);
            $member?->increment('available_hours', $data['hours']);
            $member?->increment('hours_contributed', $data['hours']);
        });

        return response()->json(['message' => 'تمت إضافة الساعات إلى الرصيد.']);
    }

    public function requestHelp(Request $request): JsonResponse
    {
        abort_unless(Auth::check(), 401, 'يجب تسجيل الدخول أولًا.');
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'category' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
        $data['requester_id'] = Auth::id();
        $data['person'] = Auth::user()->name;
        $data['initial'] = mb_substr(Auth::user()->name, 0, 1);
        $data['place'] = 'عن بُعد';
        $data['status'] = 'open';
        HelpRequest::create($data);

        return response()->json(['message' => 'تم تسجيل طلبك بنجاح.']);
    }

    public function offerHelp(HelpRequest $helpRequest): JsonResponse
    {
        abort_unless(Auth::check(), 401, 'يجب تسجيل الدخول أولًا.');
        abort_if($helpRequest->status !== 'open', 409, 'هذا الطلب لم يعد متاحًا.');
        if ($helpRequest->requester_id && $helpRequest->requester_id === Auth::id()) {
            abort(422, 'لا يمكنك تنفيذ طلبك بنفسك.');
        }
        DB::transaction(function () use ($helpRequest) {
            $helper = Auth::user()->member;
            $requester = $helpRequest->requester_id ? CommunityMember::where('user_id', $helpRequest->requester_id)->lockForUpdate()->first() : null;
            if ($requester && $requester->available_hours < 1) {
                abort(422, 'رصيد صاحب الطلب غير كافٍ حاليًا.');
            }
            $helpRequest->update(['status' => 'fulfilled', 'helper_id' => Auth::id()]);
            if ($requester) {
                $requester->decrement('available_hours');
                TimeActivity::create(['member_id' => $requester->id, 'title' => 'استخدام ساعة: '.$helpRequest->title, 'hours' => -1, 'activity_date' => now()->toDateString()]);
            }
            if ($helper) {
                $helper->increment('hours_contributed');
                $helper->increment('available_hours');
                TimeActivity::create(['member_id' => $helper->id, 'title' => 'تقديم مساعدة: '.$helpRequest->title, 'hours' => 1, 'activity_date' => now()->toDateString()]);
            }
        });

        return response()->json(['message' => 'شكرًا لك، تم تسجيل المساعدة.']);
    }
}
