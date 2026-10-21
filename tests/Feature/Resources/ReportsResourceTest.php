<?php

declare(strict_types=1);

use Carbon\Carbon;
use CXEngine\ThreeCx\Requests\Auth\TokenRequest;
use CXEngine\ThreeCx\Requests\Reports\DownloadCallLogRequest;
use CXEngine\ThreeCx\Requests\Reports\GetAgentLoginHistoryDataRequest;
use CXEngine\ThreeCx\Requests\Reports\GetCallLogDataRequest;
use CXEngine\ThreeCx\Requests\Reports\GetDetailedQueueStatisticsDataRequest;
use CXEngine\ThreeCx\Requests\Reports\GetQueuePerformanceOverviewDataRequest;
use CXEngine\ThreeCx\ThreeCxConnector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    $this->connector = new ThreeCxConnector('pbx.example.com', 'client-id', 'client-secret');

    $this->mock = new MockClient([
        TokenRequest::class                         => MockResponse::make(['access_token' => 'tok', 'expires_in' => 3600]),
        GetCallLogDataRequest::class                => MockResponse::make(['value' => []]),
        DownloadCallLogRequest::class               => MockResponse::make('col1,col2', 200, ['Content-Type' => 'text/csv']),
        GetAgentLoginHistoryDataRequest::class      => MockResponse::make(['value' => []]),
        GetDetailedQueueStatisticsDataRequest::class => MockResponse::make(['value' => []]),
        GetQueuePerformanceOverviewDataRequest::class => MockResponse::make(['value' => []]),
    ]);

    $this->connector->withMockClient($this->mock);
    $this->connector->getAccessToken(); // prime token cache
});

it('callLog dispatches GetCallLogDataRequest', function () {
    $from = Carbon::parse('2024-03-15', 'UTC');
    $to   = Carbon::parse('2024-03-16', 'UTC');

    $response = $this->connector->reports()->callLog($from, $to);

    expect($response->successful())->toBeTrue();
    $this->mock->assertSent(GetCallLogDataRequest::class);
});

it('callLogForDate dispatches GetCallLogDataRequest for the given day', function () {
    $this->connector->reports()->callLogForDate(Carbon::parse('2024-03-15', 'UTC'));

    $this->mock->assertSent(GetCallLogDataRequest::class);
});

it('downloadCallLog dispatches DownloadCallLogRequest', function () {
    $from = Carbon::parse('2024-03-15', 'UTC');
    $to   = Carbon::parse('2024-03-16', 'UTC');

    $response = $this->connector->reports()->downloadCallLog($from, $to);

    expect($response->successful())->toBeTrue();
    $this->mock->assertSent(DownloadCallLogRequest::class);
});

it('agentLoginHistory dispatches GetAgentLoginHistoryDataRequest', function () {
    $start = Carbon::parse('2024-03-15', 'UTC');
    $end   = Carbon::parse('2024-03-16', 'UTC');

    $this->connector->reports()->agentLoginHistory($start, $end);

    $this->mock->assertSent(GetAgentLoginHistoryDataRequest::class);
});

it('agentLoginHistoryForDate dispatches for the given day', function () {
    $this->connector->reports()->agentLoginHistoryForDate(Carbon::parse('2024-03-15', 'UTC'));

    $this->mock->assertSent(GetAgentLoginHistoryDataRequest::class);
});

it('detailedQueueStatistics dispatches GetDetailedQueueStatisticsDataRequest', function () {
    $start = Carbon::parse('2024-03-15', 'UTC');
    $end   = Carbon::parse('2024-03-16', 'UTC');

    $this->connector->reports()->detailedQueueStatistics($start, $end);

    $this->mock->assertSent(GetDetailedQueueStatisticsDataRequest::class);
});

it('queuePerformanceOverview dispatches GetQueuePerformanceOverviewDataRequest', function () {
    $from = Carbon::parse('2024-03-15', 'UTC');
    $to   = Carbon::parse('2024-03-16', 'UTC');

    $this->connector->reports()->queuePerformanceOverview($from, $to);

    $this->mock->assertSent(GetQueuePerformanceOverviewDataRequest::class);
});
