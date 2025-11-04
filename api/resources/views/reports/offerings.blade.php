@extends('reports._base_pdf')

@section('body')
  <h1>Listado de Ofertas</h1>
  <div class="muted">Periodo: {{ $period ?: 'Todos' }} • Generado: {{ now()->format('Y-m-d H:i') }}</div>
  <table>
    <thead>
      <tr>
        <th>Offering</th><th>Código</th><th>Nombre</th><th>Periodo</th><th>Grupo</th><th class="right">Inscritos</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $r)
        <tr>
          <td class="small">{{ $r->offering_id }}</td>
          <td>{{ $r->code }}</td>
          <td>{{ $r->name }}</td>
          <td>{{ $r->period }}</td>
          <td>{{ $r->group }}</td>
          <td class="right">{{ $r->total_enrollments }}</td>
        </tr>
      @empty
        <tr><td colspan="6">Sin resultados</td></tr>
      @endforelse
    </tbody>
  </table>
@endsection
