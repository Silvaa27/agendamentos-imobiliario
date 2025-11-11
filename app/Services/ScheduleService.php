<?php
// app/Services/ScheduleService.php

namespace App\Services;

use App\Models\BusinessHour;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ScheduleService
{
    public function getAvailableTimeSlots($date)
    {
        Log::info('=== SCHEDULE SERVICE STARTED ===', ['date' => $date]);

        try {
            $dayOfWeek = strtolower(Carbon::parse($date)->englishDayOfWeek);
            Log::info('Day of week calculated:', ['day' => $dayOfWeek]);

            $businessHour = BusinessHour::where('days', $dayOfWeek)->where('is_active', true)->first();
            
            Log::info('Business hour found:', [
                'exists' => !is_null($businessHour),
                'business_hour' => $businessHour ? $businessHour->toArray() : null
            ]);

            if (!$businessHour) {
                Log::warning('No business hour found for day:', ['day' => $dayOfWeek]);
                return [];
            }

            if (!$this->isWorkingDay($businessHour)) {
                Log::warning('Business hour is not a working day:', [
                    'morning_shift' => $this->hasMorningShift($businessHour),
                    'afternoon_shift' => $this->hasAfternoonShift($businessHour),
                    'is_active' => $businessHour->is_active
                ]);
                return [];
            }

            $availableSlots = [];
            $interval = 30; // 30 minutos entre slots

            Log::info('Generating slots for shifts:', [
                'has_morning' => $this->hasMorningShift($businessHour),
                'has_afternoon' => $this->hasAfternoonShift($businessHour)
            ]);

            // Gerar slots para o turno da manhã
            if ($this->hasMorningShift($businessHour)) {
                $morningSlots = $this->generateTimeSlots(
                    $businessHour->morning_start,
                    $businessHour->morning_end,
                    $interval,
                    $date,
                    'morning'
                );
                $availableSlots = array_merge($availableSlots, $morningSlots);
                Log::info('Morning slots generated:', ['count' => count($morningSlots)]);
            }

            // Gerar slots para o turno da tarde
            if ($this->hasAfternoonShift($businessHour)) {
                $afternoonSlots = $this->generateTimeSlots(
                    $businessHour->afternoon_start,
                    $businessHour->afternoon_end,
                    $interval,
                    $date,
                    'afternoon'
                );
                $availableSlots = array_merge($availableSlots, $afternoonSlots);
                Log::info('Afternoon slots generated:', ['count' => count($afternoonSlots)]);
            }

            Log::info('=== SCHEDULE SERVICE COMPLETED ===', [
                'total_slots' => count($availableSlots),
                'slots' => $availableSlots
            ]);

            return $availableSlots;

        } catch (\Exception $e) {
            Log::error('Error in ScheduleService:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    private function isWorkingDay($businessHour): bool
    {
        return $businessHour->is_active && ($this->hasMorningShift($businessHour) || $this->hasAfternoonShift($businessHour));
    }

    private function hasMorningShift($businessHour): bool
    {
        $hasShift = !is_null($businessHour->morning_start) && !is_null($businessHour->morning_end);
        Log::debug('Morning shift check:', [
            'start' => $businessHour->morning_start,
            'end' => $businessHour->morning_end,
            'has_shift' => $hasShift
        ]);
        return $hasShift;
    }

    private function hasAfternoonShift($businessHour): bool
    {
        $hasShift = !is_null($businessHour->afternoon_start) && !is_null($businessHour->afternoon_end);
        Log::debug('Afternoon shift check:', [
            'start' => $businessHour->afternoon_start,
            'end' => $businessHour->afternoon_end,
            'has_shift' => $hasShift
        ]);
        return $hasShift;
    }

    private function generateTimeSlots($startTime, $endTime, $interval, $date, $shiftType)
    {
        Log::debug("Generating $shiftType slots:", [
            'start' => $startTime,
            'end' => $endTime,
            'interval' => $interval,
            'date' => $date
        ]);

        $slots = [];
        
        try {
            $current = Carbon::parse($startTime);
            $end = Carbon::parse($endTime);

            Log::debug("Time parsing successful:", [
                'current' => $current->toTimeString(),
                'end' => $end->toTimeString()
            ]);

            while ($current->addMinutes($interval)->lte($end)) {
                $slotStart = $current->copy()->subMinutes($interval);
                $slotEnd = $current->copy();
                
                // Verificar se o horário já está agendado
                $isBooked = Schedule::whereDate('start_time', $date)
                    ->whereTime('start_time', $slotStart->format('H:i:s'))
                    ->where('is_available', false)
                    ->exists();

                $slotData = [
                    'start_time' => $slotStart->format('H:i'),
                    'end_time' => $slotEnd->format('H:i'),
                    'formatted' => $slotStart->format('H:i') . ' - ' . $slotEnd->format('H:i'),
                    'available' => !$isBooked,
                    'shift' => $shiftType
                ];

                $slots[] = $slotData;

                Log::debug("Slot generated:", $slotData);
            }

        } catch (\Exception $e) {
            Log::error("Error generating $shiftType slots:", [
                'message' => $e->getMessage(),
                'start_time' => $startTime,
                'end_time' => $endTime
            ]);
        }

        return $slots;
    }

    // Método para verificar se um slot específico está disponível
    public function isTimeSlotAvailable($date, $time): bool
    {
        $slots = $this->getAvailableTimeSlots($date);
        
        foreach ($slots as $slot) {
            if ($slot['start_time'] === $time && $slot['available']) {
                return true;
            }
        }
        
        return false;
    }
}