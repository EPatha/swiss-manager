import { useState } from 'react'
import type { FormEvent } from 'react'

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000/api/v1'

function App() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [token, setToken] = useState<string | null>(localStorage.getItem('openswiss_token'))
  const [error, setError] = useState<string | null>(null)

  async function signIn(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setError(null)
    const response = await fetch(`${apiBaseUrl}/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ email, password }),
    })

    if (!response.ok) {
      setError('Sign-in failed. Check your credentials and try again.')
      return
    }

    const payload = (await response.json()) as { data: { token: string } }
    localStorage.setItem('openswiss_token', payload.data.token)
    setToken(payload.data.token)
  }

  function signOut() {
    localStorage.removeItem('openswiss_token')
    setToken(null)
  }

  if (token) {
    return (
      <main className="mx-auto max-w-5xl px-6 py-10">
        <header className="flex items-center justify-between border-b border-slate-200 pb-6">
          <div>
            <p className="text-sm font-semibold uppercase tracking-wide text-blue-700">OpenSwiss</p>
            <h1 className="mt-1 text-3xl font-semibold">Tournament control desk</h1>
          </div>
          <button className="rounded border border-slate-300 px-4 py-2 text-sm" onClick={signOut}>
            Sign out
          </button>
        </header>
        <section className="mt-10 rounded-lg border border-slate-200 bg-white p-8 shadow-sm">
          <h2 className="text-xl font-semibold">Your tournaments</h2>
          <p className="mt-2 text-slate-600">
            Tournament data is loaded from the Laravel API. The tournament workflow is being built
            incrementally.
          </p>
        </section>
      </main>
    )
  }

  return (
    <main className="flex min-h-screen items-center justify-center px-6">
      <form className="w-full max-w-md rounded-lg border border-slate-200 bg-white p-8 shadow-sm" onSubmit={signIn}>
        <p className="text-sm font-semibold uppercase tracking-wide text-blue-700">OpenSwiss</p>
        <h1 className="mt-2 text-3xl font-semibold">Sign in</h1>
        <p className="mt-2 text-slate-600">Access your tournament workspace.</p>
        <label className="mt-8 block text-sm font-medium" htmlFor="email">Email</label>
        <input className="mt-2 w-full rounded border border-slate-300 px-3 py-2" id="email" type="email" required value={email} onChange={(event) => setEmail(event.target.value)} />
        <label className="mt-4 block text-sm font-medium" htmlFor="password">Password</label>
        <input className="mt-2 w-full rounded border border-slate-300 px-3 py-2" id="password" type="password" required value={password} onChange={(event) => setPassword(event.target.value)} />
        {error && <p className="mt-4 text-sm text-red-700" role="alert">{error}</p>}
        <button className="mt-6 w-full rounded bg-blue-700 px-4 py-2 font-medium text-white hover:bg-blue-800" type="submit">
          Sign in
        </button>
      </form>
    </main>
  )
}

export default App
