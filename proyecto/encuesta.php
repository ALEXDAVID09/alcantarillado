<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuesta de Servicios de Agua - AquaSolution</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e3f2fd 0%, #b3e5fc 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.2em;
            margin-bottom: 10px;
            font-weight: 300;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .form-container {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1565c0;
            font-size: 1.1em;
        }

        .form-control {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1f5fe;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #fafafa;
        }

        .form-control:focus {
            outline: none;
            border-color: #1976d2;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
        }

        .form-control:hover {
            border-color: #42a5f5;
        }

        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 40px;
        }

        .submit-btn {
            background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
            color: white;
            padding: 18px 40px;
            border: none;
            border-radius: 8px;
            font-size: 1.2em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            margin: 30px auto 0;
            min-width: 200px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(25, 118, 210, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .water-icon {
            display: inline-block;
            margin-right: 10px;
            font-size: 1.3em;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-container {
                padding: 25px;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 1.8em;
            }
        }

        .required {
            color: #f44336;
        }

        .section-title {
            color: #1565c0;
            font-size: 1.3em;
            font-weight: 600;
            margin: 30px 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #e3f2fd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><span class="water-icon">💧</span>AquaSolution</h1>
            <p>Encuesta de Calidad y Acceso a Servicios de Agua</p>
        </div>
        
        <div class="form-container">
            <form action="guardar_encuesta.php" method="POST">
                <div class="section-title">Información General</div>
                
                <div class="form-group">
                    <label for="sector">Sector donde vive <span class="required">*</span></label>
                    <input type="text" id="sector" name="sector_sin_acueducto" class="form-control" placeholder="Ingrese el nombre del sector" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="acueducto">¿Tiene acueducto? <span class="required">*</span></label>
                        <select id="acueducto" name="tiene_acueducto" class="form-control" required>
                            <option value="">Seleccione una opción</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="alcantarillado">¿Tiene alcantarillado? <span class="required">*</span></label>
                        <select id="alcantarillado" name="tiene_alcantarillado" class="form-control" required>
                            <option value="">Seleccione una opción</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                </div>

                <div class="section-title">Consumo y Suministro de Agua</div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="consumo">Consumo de agua (por día)</label>
                        <select id="consumo" name="consumo_agua" class="form-control">
                            <option value="">Seleccione el rango</option>
                            <option value="Menos de 50L">Menos de 50L</option>
                            <option value="50-100L">50-100L</option>
                            <option value="100-200L">100-200L</option>
                            <option value="Más de 200L">Más de 200L</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fuente">Fuente principal de agua</label>
                        <select id="fuente" name="fuente_agua" class="form-control">
                            <option value="">Seleccione la fuente</option>
                            <option value="Acueducto">Acueducto</option>
                            <option value="Pozo">Pozo</option>
                            <option value="Río">Río</option>
                            <option value="Agua lluvia">Agua lluvia</option>
                            <option value="Compra de agua">Compra de agua</option>
                        </select>
                    </div>
                </div>

                <div class="section-title">Problemas y Sugerencias</div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="problemas">Principales problemas que enfrenta</label>
                        <select id="problemas" name="problemas" class="form-control">
                            <option value="">Seleccione el problema principal</option>
                            <option value="Corte frecuente">Corte frecuente</option>
                            <option value="Agua sucia">Agua sucia</option>
                            <option value="Presión baja">Presión baja</option>
                            <option value="No hay servicio">No hay servicio</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="sugerencias">Sugerencias de mejora</label>
                        <select id="sugerencias" name="sugerencias" class="form-control">
                            <option value="">Seleccione una sugerencia</option>
                            <option value="Mejorar presión">Mejorar presión</option>
                            <option value="Extender cobertura">Extender cobertura</option>
                            <option value="Más revisiones">Más revisiones</option>
                            <option value="Instalación de tanques">Instalación de tanques</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <span class="water-icon">🚰</span>
                    Enviar Encuesta
                </button>
            </form>
        </div>
    </div>

    <script>
        // Agregar validación personalizada
        document.querySelector('form').addEventListener('submit', function(e) {
            const requiredFields = document.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#f44336';
                    isValid = false;
                } else {
                    field.style.borderColor = '#e1f5fe';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor, complete todos los campos obligatorios marcados con *');
            }
        });

        // Mejorar la experiencia del usuario
        document.querySelectorAll('.form-control').forEach(field => {
            field.addEventListener('change', function() {
                if (this.value) {
                    this.style.borderColor = '#4caf50';
                }
            });
        });
    </script>
</body>
</html>