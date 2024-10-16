<?php

namespace DTApi\Http\Controllers;

use DTApi\Http\Requests;
use DTApi\Repository\BookingRepository;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected $repository;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    public function index(Request $request)
    {
        $userId = $request->get('user_id');
        $authUser = $request->__authenticatedUser;

        $response = $this->repository->getJobsForUserOrAdmin($userId, $authUser);

        return $this->responseJson($response);
    }

    public function show($id)
    {
        $job = $this->repository->getJobWithRelations($id);

        return $this->responseJson($job);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->createJob($request->__authenticatedUser, $data);

        return $this->responseJson($response);
    }

    public function update($id, Request $request)
    {
        $data = $request->all();
        $response = $this->repository->updateJob($id, $data, $request->__authenticatedUser);

        return $this->responseJson($response);
    }

    public function acceptJob(Request $request)
    {
        $response = $this->repository->acceptJob($request->all(), $request->__authenticatedUser);

        return $this->responseJson($response);
    }

    public function endJob(Request $request)
    {
        $response = $this->repository->endJob($request->all());

        return $this->responseJson($response);
    }

    private function responseJson($data)
    {
        return response()->json($data);
    }
}
