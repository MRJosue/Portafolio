<h1>Nuevo contacto desde el chatbot</h1>

<p>Un cliente completo el flujo de preguntas del portafolio.</p>

<ul>
  <li><strong>Nombre:</strong> {{ $chatSession->name }}</li>
  <li><strong>Email:</strong> {{ $chatSession->email }}</li>
  <li><strong>Telefono:</strong> {{ $chatSession->phone }}</li>
  <li><strong>Proyecto:</strong> {{ $chatSession->topic }}</li>
</ul>

<p>
  Puedes continuar la conversacion desde el panel admin:
  <a href="{{ route('admin.index') }}">{{ route('admin.index') }}</a>
</p>
