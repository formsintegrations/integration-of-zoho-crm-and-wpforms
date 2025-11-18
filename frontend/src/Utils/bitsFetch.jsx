/* eslint-disable no-undef */

export default async function bitsFetch(data, action, contentType = null, queryParam = null) {
  const uri = new URL(bitwpfzc.ajaxURL)
  uri.searchParams.append('action', `bitwpfzc_${action}`)
  uri.searchParams.append('_ajax_nonce',  bitwpfzc.nonce)

  // append query params in url
  if (queryParam) {
    for (const key in queryParam) {
      if (key) {
        uri.searchParams.append(key, queryParam[key])
      }
    }
  }

  const response = await fetch(uri, {
    method: 'POST',
    headers: {
      //  'Content-Type': contentType === null ? 'application/x-www-form-urlencoded' : contentType,
    },
    body: data instanceof FormData ? data : JSON.stringify(data),
  })
    .then(res => res.json())

  return response
}
