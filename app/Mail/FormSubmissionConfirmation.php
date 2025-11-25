<?php

namespace App\Mail;

use App\Models\AdvertiseAnswer;
use App\Models\Contact;
use App\Models\Schedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class FormSubmissionConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $advertiseAnswer;
    public $contact;
    public $scheduleInfo;

    public function __construct(AdvertiseAnswer $advertiseAnswer, Contact $contact)
    {
        $this->advertiseAnswer = $advertiseAnswer;
        $this->contact = $contact;

        $schedule = Schedule::where('advertise_answer_id', $advertiseAnswer->id)->first();

        if ($schedule) {
            $this->scheduleInfo = [
                'data' => Carbon::parse($schedule->date)->format('d/m/Y'),
                'hora_inicio' => Carbon::parse($schedule->start_time)->format('H:i'),
                'hora_fim' => Carbon::parse($schedule->end_time)->format('H:i'),
                'duracao' => Carbon::parse($schedule->start_time)->diffInMinutes(Carbon::parse($schedule->end_time)) . ' minutos'
            ];
        } else {
            $this->scheduleInfo = null;
        }
    }

    public function envelope(): Envelope
    {
        $advertiseTitle = $this->advertiseAnswer->advertise->title ?? 'Formulário';
        return new Envelope(
            subject: 'Confirmação de Submissão - ' . $advertiseTitle,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.form-submission-confirmation',
            with: [
                'contactName' => $this->contact->name,
                'advertiseTitle' => $this->advertiseAnswer->advertise->title ?? 'Formulário',
                'submissionDate' => $this->advertiseAnswer->created_at->format('d/m/Y H:i'),
                'schedule' => $this->scheduleInfo,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}