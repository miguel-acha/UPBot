@extends('reports._base_pdf')

@section('body')
  <h1>Notas</h1>
  <div class="muted">
    Periodo: {{ $period ?: 'Todos' }}
    @if($offeringId) • Oferta: {{ $offeringId }} @endif
    • Generado: {{ now()->format('Y-m-d H:i') }}
  </div>
  <table>
    <thead>
      <tr>
        <th>Grade</th><th>Enrollment</th><th>Estudiante</th><th>Código</th><th>Nombre</th><th>Periodo</th><th>Grupo</th><th>Componente</th><th class="right">Nota</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $r)
        <tr>
          <td class="small">{{ $r->grade_id }}</td>
          <td class="small">{{ $r->enrollment_id }}</td>
          <td>{{ $r->student_name }}</td>
          <td>{{ $r->code }}</td>
          <td>{{ $r->name }}</td>
          <td>{{ $r->period }}</td>
          <td>{{ $r->group }}</td>
          <td>{{ $r->component }}</td>
          <td class="right">{{ is_null($r->score) ? '—' : number_format($r->score,2) }}</td>
        </tr>
      @empty
        <tr><td colspan="9">Sin resultados</td></tr>
      @endforelse
    </tbody>
  </table>
@endsection
