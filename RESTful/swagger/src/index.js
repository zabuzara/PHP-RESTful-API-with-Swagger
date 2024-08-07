import SwaggerUI from 'swagger-ui'
import 'swagger-ui/dist/swagger-ui.css';

const spec = require('./swagger-config.yaml');

const ui = SwaggerUI({
  spec,
  dom_id: '#swagger',
  defaultModelsExpandDepth: -1,
});

ui.initOAuth({
  appName: "Swagger UI Webpack Demo",
  clientId: 'implicit'
});