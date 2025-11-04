@extends('reports._base_pdf')

@section('body')
  <h1>Inscritos</h1>
  <div class="muted">
    Periodo: {{ $period ?: 'Todos' }}
    @if($offeringId) • Oferta: {{ $offeringId }} @endif
    • Generado: {{ now()->format('Y-m-d H:i') }}
  </div>
  <table>
    <thead>
      <tr>
        <th>Enrollment</th><th>Estudiante</th><th>CI</th><th>Estado</th><th>Código</th><th>Nombre</th><th>Periodo</th><th>Grupo</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $r)
        <tr>
          <td class="small">{{ $r->enrollment_id }}</td>
          <td>{{ $r->student_name }}</td>
          <td>{{ $r->student_ci }}</td>
          <td>{{ $r->status }}</td>
          <td>{{ $r->code }}</td>
          <td>{{ $r->name }}</td>
          <td>{{ $r->period }}</td>
          <td>{{ $r->group }}</td>
        </tr>
      @empty
        <tr><td colspan="8">Sin resultados</td></tr>
      @endforelse
    </tbody>
  </table>
@endsection
