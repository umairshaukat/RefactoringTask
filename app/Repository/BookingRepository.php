<?php
namespace DTApi\Repository;

use DTApi\Models\Job;
use DTApi\Models\User;
use Illuminate\Support\Facades\DB;

class BookingRepository extends BaseRepository
{
    protected $model;

    public function __construct(Job $model)
    {
        parent::__construct($model);
    }

    public function getJobsForUserOrAdmin($userId, $authUser)
    {
        if ($userId) {
            return $this->getUsersJobs($userId);
        }

        if ($this->isAdmin($authUser)) {
            return $this->getAllJobs();
        }

        return [];
    }

    public function getUsersJobs($userId)
    {
        $user = User::find($userId);
        $jobs = [];

        if ($user->is('customer')) {
            $jobs = $user->jobs()
                ->with('language', 'feedback')
                ->whereIn('status', ['pending', 'assigned', 'started'])
                ->orderBy('due', 'asc')
                ->get();
        } elseif ($user->is('translator')) {
            $jobs = Job::getTranslatorJobs($user->id, 'new');
        }

        return $this->organizeJobs($jobs, $user);
    }

    private function organizeJobs($jobs, $user)
    {
        $emergencyJobs = [];
        $normalJobs = [];

        foreach ($jobs as $job) {
            if ($job->immediate === 'yes') {
                $emergencyJobs[] = $job;
            } else {
                $normalJobs[] = $job;
            }
        }

        return [
            'emergencyJobs' => $emergencyJobs,
            'normalJobs' => $normalJobs,
            'cuser' => $user,
            'usertype' => $user->is('customer') ? 'customer' : 'translator'
        ];
    }

    public function createJob($user, array $data)
    {
        $this->validateJobData($data);

        $job = $this->prepareJobData($user, $data);
        $job->save();

        return $job;
    }

    private function validateJobData(array $data)
    {
        // Perform validation here, possibly moving to a request validator
    }

    private function prepareJobData($user, array $data)
    {
        $job = new Job();
        $job->user_id = $user->id;
        $job->from_language_id = $data['from_language_id'];
        // Assign other fields from the data array
        return $job;
    }

    public function updateJob($id, array $data, $cuser)
    {
        $job = $this->find($id);

        $this->updateJobData($job, $data, $cuser);
        $job->save();

        return $job;
    }

    private function updateJobData($job, $data, $cuser)
    {
        // Update the job based on $data and $cuser
    }

    public function acceptJob($data, $user)
    {
        $jobId = $data['job_id'];
        $job = Job::findOrFail($jobId);

        if (!$this->canAcceptJob($job, $user)) {
            return [
                'status' => 'fail',
                'message' => 'Job cannot be accepted at this time.'
            ];
        }

        $this->assignJobToTranslator($job, $user);

        return ['status' => 'success', 'job' => $job];
    }

    private function canAcceptJob($job, $user)
    {
        // Logic to check if the job can be accepted
    }

    private function assignJobToTranslator($job, $user)
    {
        // Assign the translator and update the job status
    }

    public function endJob($data)
    {
        $job = Job::findOrFail($data['job_id']);
        $job->status = 'completed';
        $job->end_at = now();
        $job->save();

        return ['status' => 'success', 'job' => $job];
    }

    private function isAdmin($user)
    {
        return in_array($user->user_type, [env('ADMIN_ROLE_ID'), env('SUPERADMIN_ROLE_ID')]);
    }
}
