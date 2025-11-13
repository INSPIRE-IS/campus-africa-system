@extends('layouts.admin')
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        .stats-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 15px;
             margin: 0px 0px 20px 0px;
        }

        .question-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            padding: 15px;
            width: 200px;
            text-align: center;
        }

        .question-card h3 {
            margin-bottom: 10px;
            color: #333;
        }

        .options {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .option {
            flex: 1 0 45%;
            margin: 5px 0;
            color: #fff;
            font-weight: bold;
            padding: 6px;
            border-radius: 6px;
            position: relative;
        }

        /* Color variations */
        .red { background: #e74c3c; }
        .green { background: #27ae60; }
        .blue { background: #2980b9; }
        .yellow { background: #f1c40f; }

        .count-circle {
            position: absolute;
            top: 6px;
            right: 6px;
            background: #000;
            color: #fff;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
    </style>
@section('content')
<div class="container" style="max-width: 1000px; margin: 30px auto;">
    <h1 style="font-size: 24px; font-weight: bold; margin-bottom: 20px;">
        Stats for {{ $stats['total']}} students
    </h1>
{{-- <div class="grid grid-cols-4 gap-4">
  @foreach($stats['questions'] as $question)
      <div class="p-4 bg-white rounded shadow">
          <h3 class="font-bold mb-2">Question {{ $question['id'] }}</h3>
          <ul>
              @foreach($question['options'] as $opt)
                  <li>{{ $opt['label'] }}: {{ $opt['count'] }}</li>
              @endforeach
          </ul>
      </div>
  @endforeach
</div> --}}
<div class="stats-container">
        @foreach($stats['questions'] as $question)
            <div class="question-card">
                <h3>Question {{ $question['id'] }}</h3>
                <div class="options">
                    @foreach($question['options'] as $index => $opt)
                        @php
                            $colors = ['red', 'green', 'blue', 'yellow'];
                            $color = $colors[$index] ?? 'gray';
                        @endphp
                        <div class="option {{ $color }}">
                            {{ $opt['label'] }}
                            <div class="count-circle">{{ $opt['count'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div style="margin-bottom: 15px;">
        <label for="year" style="margin-right: 10px;">Academic Year:</label>
        <select id="year" name="year" style="padding: 6px 10px;">
            @foreach($years as $y)
                <option value="{{ $y }}" {{ $y == $year_name ? 'selected' : '' }}>
                    {{ $y }}
                </option>
            @endforeach
        </select>

        <input
            type="text"
            id="search"
            placeholder="Start typing student name..."
            style="padding: 6px 10px; width: 250px; margin-left: 10px;"
        >
    </div>

    <div id="results">
        <p style="color: gray;">Type at least 3 letters to search for a student...</p>
    </div>

    <div id="pagination" style="margin-top: 20px; text-align: center;"></div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function() {
    let searchTimeout = null;
    let currentPage = 1;

    function fetchMatches(page = 1) {
        const query = $('#search').val();
        const year = $('#year').val();

        if (query.length < 3) {
            $('#results').html('<p style="color: gray;">Type at least 3 letters to search...</p>');
            return;
        }

        $.ajax({
            url: "{{ route('compatibility.search') }}",
            method: "GET",
            data: { query: query, year: year, page: page },
            beforeSend: function() {
                $('#results').html('<p>Loading results...</p>');
            },
            success: function(response) {
                renderResults(response);
            },
            error: function() {
                $('#results').html('<p style="color: red;">Error loading results.</p>');
            }
        });
    }

    function renderResults(response) {
        if (!response.data.length) {
            $('#results').html('<p>No matching students found.</p>');
            $('#pagination').html('');
            return;
        }

        let html = '<table border="1" cellspacing="0" cellpadding="6" style="width: 100%; border-collapse: collapse;">';
        html += '<thead><tr style="background: #f2f2f2;"><th>Student</th><th>Top 5 Matches</th></tr></thead><tbody>';

        response.data.forEach(entry => {
            html += `<tr>
                <td><strong>${entry.student.userFirstName} ${entry.student.userLastname}</strong></td>
                <td><ul style="margin: 0; padding-left: 15px;">`;

            if (entry.top_matches.length > 0) {
                entry.top_matches.forEach(match => {
                    html += `<li>${match.name} <span style="color: gray;">(${match.score}%)</span></li>`;
                });
            } else {
                html += `<li style="color: gray; font-style: italic;">No matches found</li>`;
            }

            html += `</ul></td></tr>`;
        });

        html += '</tbody></table>';

        $('#results').html(html);

        // pagination
        let pagination = '';
        if (response.last_page > 1) {
            pagination += '<div>';
            for (let i = 1; i <= response.last_page; i++) {
                pagination += `<button data-page="${i}" style="margin: 2px; padding: 4px 8px; ${i === response.current_page ? 'background:#007bff; color:white;' : ''}">${i}</button>`;
            }
            pagination += '</div>';
        }
        $('#pagination').html(pagination);
    }

    $('#search').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => fetchMatches(1), 400);
    });

    $('#year').on('change', function() {
        fetchMatches(1);
    });

    $('#pagination').on('click', 'button', function() {
        const page = $(this).data('page');
        fetchMatches(page);
    });
});
</script>
@endsection