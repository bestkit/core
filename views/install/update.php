<h2>Update Bestkit</h2>

<p>Enter your database password to update Bestkit. Before you proceed, you should <strong>back up your database</strong>. If you have any trouble, get help on the <a href="https://docs.bestkit.cn/update" target="_blank">Bestkit website</a>.</p>

<form method="post">
  <div id="error" style="display:none"></div>

  <div class="FormGroup">
    <div class="FormField">
      <label>Database Password</label>
      <input type="password" name="databasePassword">
    </div>
  </div>

  <div class="FormButtons">
    <button type="submit">Update Bestkit</button>
  </div>
</form>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('form input').select();

    document.querySelector('form').addEventListener('submit', function(e) {
      e.preventDefault();

      var button = this.querySelector('button');
      button.textContent = 'Please Wait...';
      button.disabled = true;

      fetch('', {
        method: 'POST',
        body: new FormData(this)
      })
        .then(response => {
          if (response.ok) {
            window.location.reload();
          } else {
            response.text().then(errorMessage => {
              var error = document.querySelector('#error');
              error.style.display = 'block';
              error.textContent = 'Something went wrong:\n\n' + errorMessage;
              button.disabled = false;
              button.textContent = 'Update Bestkit';
            });
          }
        })
        .catch(error => {
          console.error('Error:', error);
        });

      return false;
    });
  });
</script>

