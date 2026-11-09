<?php // SCREEN: registration with HOA matching + Data Privacy consent. ?>
<div class="card mid"><h2>Create your account</h2>
<p class="muted">Type your <b>full name and Block/Lot exactly as in the HOA records</b> for instant approval. If it does not match exactly, an HOA admin reviews it; nobody is rejected automatically.</p>
<?= form('register','','enctype="multipart/form-data"') ?><div class="f wide">
<label class="full">Full name<input name="name" required placeholder="e.g. Juan Roberto Dela Cruz Jr."></label>
<label>Block<input name="block" required placeholder="3"></label>
<label>Lot<input name="lot" required placeholder="12"></label>
<label class="full">Street<input name="street" placeholder="Soluna Executive Village street"></label>
<label>Email<input type="email" name="email" required></label>
<label>Mobile number<input name="phone" placeholder="09xx xxx xxxx"></label>
<label class="full">Password<input type="password" name="pass" required minlength="6"></label>
<label class="full">Proof of residence <small>(optional: utility bill or village ID photo, JPG/PNG/PDF, max 5 MB. Only HOA admins can see it.)</small><input type="file" name="proof" accept=".jpg,.jpeg,.png,.webp,.pdf"></label>
<label class="check full"><input type="checkbox" name="consent" required> I consent to the processing of my personal data under the Data Privacy Act of 2012 (RA 10173) for HOA verification, trips, orders and payments only.</label>
<div class="full"><button data-tour="register-submit">Register</button></div></div></form>
<p class="center"><small>Already registered? <a href="?p=login">Log in</a></small></p></div>
