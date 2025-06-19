@foreach ($results as $result)
    <div style="margin-bottom: 15px;">
        <p style="color: {{ $result['match'] ? 'red' : 'black' }}">
            <strong>Câu:</strong> {{ $result['sentence'] }}
        </p>

        @if ($result['match'])
            <p>→ Đã phát hiện trùng lặp ({{ count($result['urls']) }} nguồn):</p>
            <ul>
                @foreach ($result['urls'] as $url)
                    <li><a href="{{ $url }}" target="_blank">{{ $url }}</a></li>
                @endforeach
            </ul>
        @else
            <p>→ Không phát hiện trùng lặp</p>
        @endif
    </div>
@endforeach

<hr>
<p><strong>Tổng tỉ lệ trùng:</strong> {{ $percent }}%</p>

@if ($percent >= 70)
    <p style="color: red;"><strong>Mức độ:</strong> Cảnh báo cao</p>
@elseif ($percent >= 40)
    <p style="color: orange;"><strong>Mức độ:</strong> Trung bình</p>
@elseif ($percent >= 20)
    <p style="color: green;"><strong>Mức độ:</strong> Nhẹ</p>
@else
    <p style="color: gray;"><strong>Mức độ:</strong> An toàn</p>
@endif
