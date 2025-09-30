<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>La Cocina de Abuela - Menú Digital</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: #fff8f0;
      color: #333;
    }

    header {
      background-color: #b71c1c;
      color: white;
      text-align: center;
      padding: 30px 20px;
    }

    header h1 {
      margin: 0;
      font-size: 2.5em;
    }

    header p {
      margin: 10px 0 0;
      font-size: 1.1em;
    }

    .menu {
      max-width: 800px;
      margin: 30px auto;
      padding: 0 20px;
    }

    .section-title {
      border-bottom: 3px solid #b71c1c;
      font-size: 1.8em;
      margin-top: 40px;
      margin-bottom: 10px;
      padding-bottom: 5px;
    }

    .item {
      margin-bottom: 20px;
    }

    .item h3 {
      margin: 0;
      font-size: 1.2em;
      color: #b71c1c;
    }

    .item p {
      margin: 5px 0;
    }

    .price {
      font-weight: bold;
      color: #444;
    }

    .whatsapp-btn {
      display: block;
      width: fit-content;
      margin: 40px auto;
      padding: 12px 25px;
      background-color: #25D366;
      color: white;
      text-decoration: none;
      font-size: 1.1em;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }

    footer {
      text-align: center;
      font-size: 0.9em;
      color: #777;
      margin: 50px 0 20px;
    }

    @media (max-width: 600px) {
      header h1 {
        font-size: 2em;
      }
      .section-title {
        font-size: 1.5em;
      }
    }
  </style>
</head>
<body>

  <header>
    <h1>La Cocina de Abuela</h1>
    <p>Menú tradicional con sabor casero</p>
  </header>

  <section class="menu">
    <div class="section-title">🥑 Entradas</div>
    <div class="item">
      <h3>Guacamole con totopos</h3>
      <p>Cremoso guacamole acompañado de totopos crujientes.</p>
      <p class="price">$65 MXN</p>
    </div>
    <div class="item">
      <h3>Sopa de tortilla</h3>
      <p>Calientita y con queso rallado, aguacate y crema.</p>
      <p class="price">$75 MXN</p>
    </div>

    <div class="section-title">🌮 Platos Fuertes</div>
    <div class="item">
      <h3>Enchiladas verdes</h3>
      <p>Rellenas de pollo, bañadas en salsa verde con crema.</p>
      <p class="price">$120 MXN</p>
    </div>
    <div class="item">
      <h3>Tacos de cochinita</h3>
      <p>3 tacos con cebolla morada, servidos con salsa de la casa.</p>
      <p class="price">$95 MXN</p>
    </div>

    <div class="section-title">🍹 Bebidas</div>
    <div class="item">
      <h3>Agua de horchata</h3>
      <p>Refrescante y natural, con toque de canela.</p>
      <p class="price">$30 MXN</p>
    </div>
    <div class="item">
      <h3>Jamaica</h3>
      <p>Infusión natural fría con un poco de limón.</p>
      <p class="price">$30 MXN</p>
    </div>
  </section>

  <a href="https://wa.me/5215555555555" target="_blank" class="whatsapp-btn">
    📲 Pedir o consultar por WhatsApp
  </a>

  <footer>
    © 2025 La Cocina de Abuela - Menú digital por [Tu Nombre]
  </footer>

</body>
</html>
