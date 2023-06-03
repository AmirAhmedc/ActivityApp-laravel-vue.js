<template>
  <div class="wrapper">
    <div class="left">
      <div class="btn-left">
        <button class="rotate-button" @click="rotateLeft"><i class="fa fa-rotate-left fa-2x"></i></button>
      </div>
      <div class="btn-right">
        <button class="rotate-button" @click="rotateRight"><i class="fa fa-rotate-right fa-2x"></i></button>
      </div>
      <div class="btn-reset">
        <button type="button" class="reset-button" @click="resetCanvas">
          <i class="fa-solid fa-eraser fa-2x"></i>
        </button>
      </div>
    </div>

    <div class="center">
      <div class="content">
        <div class="box-message">
          <div v-show="successMessage" class="alert alert-success alert-white rounded">
            <button type="button" @click="hideSuccessMessage" class="close" data-dismiss="alert"
              aria-hidden="true">×</button>
            <div class="icon"><i class="fa fa-check"></i></div>
            <strong>Success!</strong> {{ successMessage }}
          </div>
        </div>
      </div>
      <div id="container">
        <div id="monitor">
          <div id="monitorscreen">
            <canvas ref="canvas"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="right">
      <form @submit.prevent="uploadImages">
        <div class="inputs-form">
          <div class="input-group btn-box">
            <input v-show="false" type="file" class="custom-file-input" name="backgroundImage" id="backgroundImage"
              accept="image/*" @change="onBackgroundImageChange" required>
            <label class="custom-file-label input-group-text color1" for="backgroundImage"><i
                class="fa fa-image fa-2x text-primary "></i></label>
          </div>
          <div class="input-group btn-box">
            <input v-show="false" type="file" class="custom-file-input" name="foregroundImage" id="foregroundImage"
              accept="image/*" @change="onForegroundImageChange" required>
            <label class="custom-file-label input-group-text color2" for="foregroundImage"><i
                class="fa fa-camera fa-2x text-primary"></i></label>
          </div>
          <div class="btn-box">
            <button type="submit" class="upload-button"><i class="fa fa-upload fa-2x text-primary"></i></button>
          </div>
          <div class="btn-box">
            <button class="download-button" @click="downloadImage"><i class="fa fa-download fa-2x text-primary"></i></button>
          </div>
        </div>
      </form>

    </div>
  </div>
  <div class="below">
    <div class="below-icon">
      <button class="size-button" @click="increaseWidth"><i class="fa fa-arrows-left-right fa-2x"></i></button>
    </div>
    <div class="below-icon">
      <button class="size-button" @click="decreaseWidth"><i class="fa fa-arrow-right fa-2x"></i></button>
    </div>
    <div class="below-icon">
      <button class="size-button" @click="increaseHeight"><i class="fa fa-arrows-up-down fa-2x"></i></button>
    </div>
    <div class="below-icon">
      <button class="size-button" @click="decreaseHeight"><i class="fa fa-arrow-down fa-2x text-primary"></i></button>
    </div>
  </div>
</template>


<script>
import axios from 'axios';

export default {
  data() {
    return {
      backgroundImage: null,
      foregroundImage: null,
      canvasWidth: 800,
      canvasHeight: 600,
      successMessage: null,
      rotationAngle: 0,
      imgWidth: 1,
      imgHeight: 1,

    };
  },

  methods: {
    async downloadImage() {
      try {
        const response = await fetch('/api/download-image');
        const blob = await response.blob();
        const url = URL.createObjectURL(blob);

        const link = document.createElement('a');
        link.href = url;
        link.download = 'workspace.jpg';
        link.setAttribute('download', 'workspace.jpg');
        link.click();

        URL.revokeObjectURL(url);
      } catch (error) {
        console.log(error);
      }
    },

    async uploadImages() {
      const canvas = this.$refs.canvas;
      const formData = new FormData();

      // generate blob from combined image
      canvas.toBlob((blob) => {
        // append blob to form data with filename and content type
        formData.append('image', blob, 'workspace.jpg', { type: 'image/jpeg' });

        // append background and foreground images to form data
        formData.append('backgroundImage', this.backgroundImage);
        formData.append('foregroundImage', this.foregroundImage);
        // append canvas dimensions to form data
        formData.append('width', this.canvasWidth);
        formData.append('height', this.canvasHeight);
        // append rotation angle to form data
        formData.append('rotationAngle', this.rotationAngle);

        // send form data to server
        axios.post('/api/upload-images', formData)
          .then((response) => {
            console.log(response.data);
            this.successMessage = "Images uploaded successfully!";
          })
          .catch((error) => {
            console.log(error);
          });
      }, 'image/jpeg', 0.9);
    },

    onBackgroundImageChange(event) {
      this.backgroundImage = event.target.files[0];
      this.drawCanvas();
    },

    onForegroundImageChange(event) {
      this.foregroundImage = event.target.files[0];
      this.drawCanvas();
    },

    drawCanvas() {
      const canvas = this.$refs.canvas;
      const context = canvas.getContext('2d');

      context.clearRect(0, 0, canvas.width, canvas.height);

      if (this.backgroundImage) {
        const background = new Image();
        background.onload = () => {
          canvas.width = background.width;
          canvas.height = background.height;

          if (!this.foregroundImage) {
            const aspectRatio = background.width / background.height;
            const canvasAspectRatio = canvas.width / canvas.height;

            let width, height, x, y;

            if (aspectRatio > canvasAspectRatio) {
              // Background image is wider than canvas
              width = canvas.width * this.imgWidth;
              height = Math.floor(canvas.width / aspectRatio) * this.imgHeight;
              x = (canvas.width - width) / 2;
              y = (canvas.height - height) / 2;
            } else {
              // Background image is taller than canvas
              height = canvas.height * this.imgHeight;
              width = Math.floor(canvas.height * aspectRatio) * this.imgWidth;
              x = (canvas.width - width) / 2;
              y = (canvas.height - height) / 2;
            }
            // rotate the background image if the foreground image is not uploaded
            context.save();
            context.translate(canvas.width / 2, canvas.height / 2);
            context.rotate(this.rotationAngle * Math.PI / 180);
            context.drawImage(background, -width / 2, -height / 2, width, height);
            context.restore();
          } else {
            context.drawImage(background, 0, 0);

            const foreground = new Image();
            foreground.onload = () => {
              // calculate position and size of foreground image to fit within canvas
              const aspectRatio = foreground.width / foreground.height;
              const canvasAspectRatio = canvas.width / canvas.height;

              let width, height, x, y;

              if (aspectRatio > canvasAspectRatio) {
                // foreground image is wider than canvas
                width = (canvas.width / 1.1) * this.imgWidth;
                height = ((canvas.width)) * this.imgHeight;
              } else {
                // foreground image is taller than canvas
                width = ((canvas.height / 1.2) / aspectRatio) * this.imgWidth;
                height = (canvas.height / 1.05) * this.imgHeight;

              }

              context.save();
              context.translate(canvas.width / 2, canvas.height / 2);
              context.rotate(this.rotationAngle * Math.PI / 180);
              context.drawImage(foreground, -width / 2, -height / 2, width, height);
              context.restore();
            };
            foreground.src = URL.createObjectURL(this.foregroundImage);
          }
        };
        background.src = URL.createObjectURL(this.backgroundImage);
      }
    },

    rotateLeft() {
      this.rotationAngle -= 90;
      this.drawCanvas();
    },

    rotateRight() {
      this.rotationAngle += 90;
      this.drawCanvas();
    },

    increaseWidth() {
      this.imgWidth *= 1.1;
      this.drawCanvas();
    },

    decreaseWidth() {
      this.imgWidth /= 1.1;
      this.drawCanvas();
    },
    increaseHeight() {
      this.imgHeight *= 1.1;
      this.drawCanvas();
    },

    decreaseHeight() {
      this.imgHeight /= 1.1;
      this.drawCanvas();
    },
    resetCanvas() {
      // reset canvas and uploaded images
      this.backgroundImage = null;
      this.foregroundImage = null;
      this.rotationAngle = 0;
      this.imgWidth = 1;
      this.imgHeight = 1;
      this.successMessage = null;

      const formData = new FormData();

      // redraw canvas
      this.drawCanvas();
    },
    hideSuccessMessage() {
      this.successMessage = null;
    },
  },

  mounted() {
    this.$refs.canvas.width = this.canvasWidth;
    this.$refs.canvas.height = this.canvasHeight;
    this.drawCanvas();
  },
}
</script>
