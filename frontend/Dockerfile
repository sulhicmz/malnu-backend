# Frontend development Dockerfile
# Provides hot-reload Vite development server

FROM node:20-alpine

# Set working directory
WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm ci

# Copy the rest of the application
COPY . .

# Expose Vite dev server port
EXPOSE 5173

# Start Vite dev server with hot reload
# --host 0.0.0.0 allows connections from outside the container
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
