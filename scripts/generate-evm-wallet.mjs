import { Wallet } from "ethers";

const wallet = Wallet.createRandom();

const payload = {
  address: wallet.address,
  privateKey: wallet.privateKey,
  mnemonic: wallet.mnemonic?.phrase ?? null,
  derivationPath: wallet.mnemonic?.path ?? "m/44'/60'/0'/0/0",
};

if (!payload.mnemonic) {
  console.error("Mnemonic generation failed.");
  process.exit(1);
}

process.stdout.write(JSON.stringify(payload));
