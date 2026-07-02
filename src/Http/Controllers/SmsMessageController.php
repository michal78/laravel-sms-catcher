<?php

namespace SmsCatcher\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use SmsCatcher\Storage\MessageRepository;

class SmsMessageController extends Controller
{
    public function __construct(
        protected MessageRepository $repository,
        protected Factory $view,
    ) {}

    public function index(): View
    {
        return $this->view->make('sms-catcher::index', [
            'messages' => $this->repository->all(),
        ]);
    }

    public function api(): JsonResponse
    {
        return response()->json([
            'messages' => $this->repository->all(),
        ]);
    }

    public function show(string $id): View
    {
        $message = $this->repository->markAsRead($id);

        abort_if($message === null, 404);

        return $this->view->make('sms-catcher::show', [
            'message' => $message,
        ]);
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->repository->delete($id);

        return redirect()->route('sms-catcher.index');
    }

    public function clear(): RedirectResponse
    {
        $this->repository->clear();

        return redirect()->route('sms-catcher.index');
    }
}
