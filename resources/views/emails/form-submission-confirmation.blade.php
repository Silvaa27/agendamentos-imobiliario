<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Confirmação de Submissão</title>
    <style>
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            color: #374151;
            max-width: 600px;
            margin: 0 auto;
            padding: 0;
            background-color: #f9fafb;
        }

        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 20px;
        }

        .header {
            background: linear-gradient(135deg, #3b82f6, #3b82f6); /* Primary-500 */
            color: white;
            padding: 32px;
            text-align: center;
        }

        .header h1 {
            margin: 0 0 8px 0;
            font-size: 28px;
            font-weight: 700;
        }

        .header p {
            margin: 0;
            font-size: 16px;
            opacity: 0.9;
        }

        .content {
            padding: 32px;
        }

        .highlight {
            background: #f8fafc;
            border-left: 4px solid #3b82f6; /* Primary-500 */
            padding: 24px;
            margin: 24px 0;
            border-radius: 0 8px 8px 0;
        }

        .schedule-box {
            background: #eff6ff; /* Primary-50 */
            border: 2px solid #3b82f6; /* Primary-500 */
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
        }

        .footer {
            background: #f1f5f9;
            padding: 24px;
            text-align: center;
            color: #64748b;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }

        .info-item {
            margin: 12px 0;
            display: flex;
        }

        .info-label {
            font-weight: 600;
            color: #475569;
            min-width: 160px;
        }

        .schedule-item {
            display: flex;
            align-items: center;
            margin: 10px 0;
            font-size: 16px;
        }

        .schedule-icon {
            margin-right: 12px;
            font-size: 18px;
        }

        ul {
            padding-left: 24px;
            margin: 16px 0;
        }

        li {
            margin-bottom: 10px;
            font-size: 15px;
        }

        .no-schedule {
            background: #fef3c7; /* Warning-100 */
            border: 1px solid #f59e0b; /* Warning-500 */
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            color: #92400e; /* Warning-800 */
            margin: 24px 0;
        }

        .btn {
            display: inline-block;
            background: #3b82f6; /* Primary-500 */
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 8px 4px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>✅ Confirmação de Submissão</h1>
            <p>Obrigado pela sua participação!</p>
        </div>

        <div class="content">
            <p>Olá <strong>{{ $contactName }}</strong>,</p>

            <p>Recebemos com sucesso a sua submissão para o formulário:</p>

            <div class="highlight">
                <h3 style="margin: 0 0 16px 0; color: #3b82f6; font-size: 20px;">{{ $advertiseTitle }}</h3>
                <div class="info-item">
                    <span class="info-label">Data de submissão:</span> {{ $submissionDate }}
                </div>
                <div class="info-item">
                    <span class="info-label">Número de referência:</span> {{ $referenceNumber }}
                </div>
            </div>

            <!-- Secção da Marcação -->
            @if ($schedule && is_array($schedule) && !empty($schedule['data']))
                <div class="schedule-box">
                    <h3 style="margin: 0 0 20px 0; color: #3b82f6; text-align: center; font-size: 20px;">
                        📅 Detalhes da Sua Marcação
                    </h3>

                    <div class="schedule-item">
                        <span class="schedule-icon">📅</span>
                        <strong>Data:</strong> {{ $schedule['data'] }}
                    </div>

                    <div class="schedule-item">
                        <span class="schedule-icon">🕒</span>
                        <strong>Hora de Início:</strong> {{ $schedule['hora_inicio'] }}
                    </div>

                    <div class="schedule-item">
                        <span class="schedule-icon">⏰</span>
                        <strong>Hora de Fim:</strong> {{ $schedule['hora_fim'] }}
                    </div>

                    <div class="schedule-item">
                        <span class="schedule-icon">⏱️</span>
                        <strong>Duração:</strong> {{ $schedule['duracao'] }}
                    </div>

                    <div style="margin-top: 20px; padding: 16px; background: #dbeafe; border-radius: 8px;">
                        <strong>💡 Lembrete:</strong> Por favor, esteja presente 5 minutos antes da hora marcada.
                    </div>
                </div>
            @else
                <div class="no-schedule">
                    <p style="margin: 0 0 8px 0; font-size: 16px;">⚠️ <strong>Sem marcação associada</strong></p>
                    <p style="margin: 0; font-size: 14px;">Não foi encontrada nenhuma marcação de horário para esta
                        submissão.</p>
                </div>
            @endif

            <p style="font-size: 16px; margin: 24px 0 16px 0;"><strong>Próximos passos:</strong></p>
            <ul>
                <li>A nossa equipa irá analisar a sua submissão</li>
                @if ($schedule && is_array($schedule) && !empty($schedule['data']))
                    <li>Confirmaremos a sua marcação por telefone ou email</li>
                    <li>Em caso de necessidade de alteração, entraremos em contacto</li>
                @else
                    <li>Entraremos em contacto consigo brevemente para agendar</li>
                @endif
                <li>Mantenha o seu email e telefone disponíveis</li>
            </ul>

            <p style="margin: 24px 0;">Se tiver alguma questão urgente, não hesite em contactar-nos através dos canais
                habituais.</p>

            <p style="margin-top: 32px; font-size: 15px;">
                Atenciosamente,<br>
                <strong style="font-size: 16px;">A Equipa {{ config('app.name', 'Laravel') }}</strong>
            </p>
        </div>

        <div class="footer">
            <p style="margin: 0 0 8px 0;">&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. Todos os
                direitos reservados.</p>
            <p style="margin: 0; font-size: 13px;">Este é um email automático. Por favor não responda a esta mensagem.
            </p>
        </div>
    </div>
</body>

</html>