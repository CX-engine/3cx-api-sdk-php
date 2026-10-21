<?php

declare(strict_types=1);

namespace CXEngine\ThreeCx\Resources;

use Carbon\CarbonInterface;
use Saloon\Http\Response;
use CXEngine\ThreeCx\Requests\Recordings\DownloadRecordingRequest;
use CXEngine\ThreeCx\Requests\Recordings\FindRecordingRequest;
use CXEngine\ThreeCx\Requests\Recordings\ListRecordingsRequest;
use CXEngine\ThreeCx\Requests\Recordings\TranscribeRecordingsRequest;

class RecordingsResource extends Resource
{
    /**
     * List recordings for a date range, optionally filtered by caller/callee number.
     *
     * Key fields per row: `Id`, `StartTime`, `FromDn`, `FromCallerNumber`, `ToDn`,
     * `ToCallerNumber`, `IsTranscribed`, `CanBeTranscribed`, `Transcription`,
     * `Summary`, `SentimentScore`, `TranscriptionResult`.
     *
     * Note: `contains()` on phone numbers may be rejected on some PBX builds.
     * If so, omit `$callerNumber`/`$calleeNumber` and post-filter in PHP.
     */
    public function list(
        CarbonInterface $from,
        CarbonInterface $to,
        ?string $callerNumber = null,
        ?string $calleeNumber = null,
        int $top = 200,
    ): Response {
        return $this->connector->send(new ListRecordingsRequest(
            from: $from,
            to: $to,
            callerNumber: $callerNumber,
            calleeNumber: $calleeNumber,
            top: $top,
        ));
    }

    /**
     * Fetch a single recording by its ID, selecting all transcription fields.
     *
     * Use `->json('value.0')` to get the row directly. Includes `Transcription`,
     * `Summary`, `SentimentScore`, and `IsTranscribed`.
     */
    public function find(int $id): Response
    {
        return $this->connector->send(new FindRecordingRequest($id));
    }

    /**
     * Download the audio file for a recording. Use `->body()` on the response.
     */
    public function download(int $recId): Response
    {
        return $this->connector->send(new DownloadRecordingRequest($recId));
    }

    /**
     * Trigger on-demand transcription for one or more recordings (POST).
     *
     * Requires transcription enabled on the PBX and an AI provider configured.
     * Poll `find()` until `IsTranscribed` is `true` before reading results.
     *
     * @param int[] $ids
     */
    public function transcribe(array $ids): Response
    {
        return $this->connector->send(new TranscribeRecordingsRequest($ids));
    }
}
