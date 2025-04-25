<table class="w-100 border-collapse border border-gray-300" style="width: 100%">
    <tbody>
        @foreach($record->feedbackDetails as $detail)
            <tr>
                <td class="border border-gray-300 px-4 py-2">
                    {{ $detail->reviewQuestion->question ?? '—' }}
                </td>
                <td class="border border-gray-300 px-4 py-2">
                    @if($detail->rating)
                        {{ $detail->rating }} ⭐️
                    @else
                    {{ $detail->QuestionOption->text ?? '—' }}
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>